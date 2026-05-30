<?php

namespace App\Livewire;

use App\Models\Estetic\Appointment as EsteticAppointment;
use App\Models\Estetic\Payment as EsteticPayment;
use App\Models\Kine\Appointment as KineAppointment;
use App\Models\Kine\Payment as KinePayment;
use App\Models\Payment;
use App\Models\Person;
use App\Models\Subscription;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $weekAgo = $today->copy()->subDays(6);

        // KPIs
        $personas = Person::count();
        $suscripcionesActivas = Subscription::where('status', 'active')->count();

        $citasHoyKine = KineAppointment::whereDate('inicio', $today)->count();
        $citasHoyEstetic = EsteticAppointment::whereDate('inicio', $today)->count();
        $citasHoy = $citasHoyKine + $citasHoyEstetic;

        $ingresosGym = (float) Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
            ->where('status', 'completed')->sum('amount');
        $ingresosKine = (float) KinePayment::whereBetween('fecha', [$monthStart, $monthEnd])
            ->where('estado', 'pagado')->sum('monto');
        $ingresosEstetic = (float) EsteticPayment::whereBetween('fecha', [$monthStart, $monthEnd])
            ->where('estado', 'pagado')->sum('monto');
        $ingresosTotal = $ingresosGym + $ingresosKine + $ingresosEstetic;

        // Serie 7 días (ingresos totales por día)
        $serie = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $weekAgo->copy()->addDays($i);
            $gym = (float) Payment::whereDate('payment_date', $d)->where('status', 'completed')->sum('amount');
            $kine = (float) KinePayment::whereDate('fecha', $d)->where('estado', 'pagado')->sum('monto');
            $est = (float) EsteticPayment::whereDate('fecha', $d)->where('estado', 'pagado')->sum('monto');
            $serie[] = [
                'label' => $d->isoFormat('ddd d'),
                'gym' => $gym,
                'kine' => $kine,
                'est' => $est,
                'total' => $gym + $kine + $est,
            ];
        }
        $serieMax = max(array_column($serie, 'total')) ?: 1;

        // Próximas citas (hoy)
        $proximasKine = KineAppointment::with(['kineProfile.person', 'professional'])
            ->whereDate('inicio', $today)
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->orderBy('inicio')->limit(5)->get()
            ->map(fn ($a) => [
                'type' => 'kine',
                'inicio' => $a->inicio,
                'person' => $a->kineProfile?->person?->full_name ?? '—',
                'professional' => $a->professional?->full_name,
                'estado' => $a->estado,
                'url' => route('admin.kine.appointments.edit', $a),
            ]);
        $proximasEst = EsteticAppointment::with(['esteticProfile.person', 'professional'])
            ->whereDate('inicio', $today)
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->orderBy('inicio')->limit(5)->get()
            ->map(fn ($a) => [
                'type' => 'estetic',
                'inicio' => $a->inicio,
                'person' => $a->esteticProfile?->person?->full_name ?? '—',
                'professional' => $a->professional?->full_name,
                'estado' => $a->estado,
                'url' => route('admin.estetic.appointments.edit', $a),
            ]);
        $proximas = $proximasKine->concat($proximasEst)->sortBy('inicio')->take(8)->values();

        // Suscripciones por vencer (7 días)
        $porVencer = Subscription::with(['person', 'plan'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(7)])
            ->orderBy('end_date')->limit(6)->get();

        // Pagos recientes (últimos 5 agregados)
        $pagosRecientes = collect()
            ->concat(Payment::with('person')->latest('payment_date')->limit(5)->get()->map(fn ($p) => [
                'type' => 'gym',
                'person' => $p->person?->full_name ?? '—',
                'amount' => (float) $p->amount,
                'date' => $p->payment_date,
            ]))
            ->concat(KinePayment::with('kineProfile.person')->latest('fecha')->limit(5)->get()->map(fn ($p) => [
                'type' => 'kine',
                'person' => $p->kineProfile?->person?->full_name ?? '—',
                'amount' => (float) $p->monto,
                'date' => $p->fecha,
            ]))
            ->concat(EsteticPayment::with('esteticProfile.person')->latest('fecha')->limit(5)->get()->map(fn ($p) => [
                'type' => 'estetic',
                'person' => $p->esteticProfile?->person?->full_name ?? '—',
                'amount' => (float) $p->monto,
                'date' => $p->fecha,
            ]))
            ->sortByDesc('date')->take(6)->values();

        // Distribución de citas de la semana por estado
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $estados = ['pendiente', 'confirmado', 'atendido', 'cancelado', 'ausente'];
        $distribucion = [];
        foreach ($estados as $e) {
            $k = KineAppointment::whereBetween('inicio', [$weekStart, $weekEnd])->where('estado', $e)->count();
            $s = EsteticAppointment::whereBetween('inicio', [$weekStart, $weekEnd])->where('estado', $e)->count();
            $distribucion[$e] = $k + $s;
        }
        $totalSemana = array_sum($distribucion) ?: 1;

        return view('livewire.dashboard', [
            'personas' => $personas,
            'suscripcionesActivas' => $suscripcionesActivas,
            'citasHoy' => $citasHoy,
            'citasHoyKine' => $citasHoyKine,
            'citasHoyEstetic' => $citasHoyEstetic,
            'ingresosTotal' => $ingresosTotal,
            'ingresosGym' => $ingresosGym,
            'ingresosKine' => $ingresosKine,
            'ingresosEstetic' => $ingresosEstetic,
            'serie' => $serie,
            'serieMax' => $serieMax,
            'proximas' => $proximas,
            'porVencer' => $porVencer,
            'pagosRecientes' => $pagosRecientes,
            'distribucion' => $distribucion,
            'totalSemana' => $totalSemana,
        ]);
    }
}
