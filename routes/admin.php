<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Admin\People\Index as PeopleIndex;
use App\Livewire\Admin\People\Show as PeopleShow;
use App\Livewire\Admin\People\Edit as PeopleEdit;
use App\Livewire\Admin\People\Clinical as PeopleClinical;

use App\Livewire\Admin\Admission\Wizard as AdmissionWizard;

use App\Livewire\Admin\Agenda\WeeklyView as AgendaWeekly;

use App\Livewire\Admin\Plans\Index as PlansIndex;
use App\Livewire\Admin\Plans\Create as PlansCreate;
use App\Livewire\Admin\Plans\Edit as PlansEdit;
use App\Livewire\Admin\Plans\Show as PlansShow;

use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Livewire\Admin\Subscriptions\Create as SubscriptionsCreate;

use App\Livewire\Admin\Payments\Index as PaymentsIndex;
use App\Livewire\Admin\Payments\Create as PaymentsCreate;

use App\Livewire\Admin\Kine\TiposTratamientos\Index as KineTiposIndex;
use App\Livewire\Admin\Kine\TiposTratamientos\Create as KineTiposCreate;
use App\Livewire\Admin\Kine\TiposTratamientos\Edit as KineTiposEdit;
use App\Livewire\Admin\Kine\Treatments\Index as KineTreatmentsIndex;
use App\Livewire\Admin\Kine\Treatments\Form as KineTreatmentsForm;
use App\Livewire\Admin\Kine\Appointments\Index as KineAppointmentsIndex;
use App\Livewire\Admin\Kine\Appointments\Form as KineAppointmentsForm;
use App\Livewire\Admin\Kine\Payments\Index as KinePaymentsIndex;
use App\Livewire\Admin\Kine\Payments\Create as KinePaymentsCreate;
use App\Livewire\Admin\Kine\Patients\Index as KinePatientsIndex;
use App\Livewire\Admin\Kine\Patients\Show as KinePatientsShow;
use App\Livewire\Admin\Kine\Protocols\Apply as KineProtocolApply;
use App\Livewire\Admin\Kine\Sessions\Attend as KineSessionAttend;

use App\Livewire\Admin\Estetic\TiposTratamientos\Index as EsteticTiposIndex;
use App\Livewire\Admin\Estetic\TiposTratamientos\Create as EsteticTiposCreate;
use App\Livewire\Admin\Estetic\TiposTratamientos\Edit as EsteticTiposEdit;
use App\Livewire\Admin\Estetic\Treatments\Index as EsteticTreatmentsIndex;
use App\Livewire\Admin\Estetic\Treatments\Form as EsteticTreatmentsForm;
use App\Livewire\Admin\Estetic\Appointments\Index as EsteticAppointmentsIndex;
use App\Livewire\Admin\Estetic\Appointments\Form as EsteticAppointmentsForm;
use App\Livewire\Admin\Estetic\Payments\Index as EsteticPaymentsIndex;
use App\Livewire\Admin\Estetic\Payments\Create as EsteticPaymentsCreate;
use App\Livewire\Admin\Estetic\Patients\Index as EsteticPatientsIndex;
use App\Livewire\Admin\Estetic\Patients\Show as EsteticPatientsShow;
use App\Livewire\Admin\Estetic\Protocols\Apply as EsteticProtocolApply;
use App\Livewire\Admin\Estetic\Sessions\Attend as EsteticSessionAttend;

use App\Livewire\Admin\Cash\Daily as CashDaily;
use App\Livewire\Admin\Whatsapp\Templates as WhatsappTemplates;
use App\Livewire\Admin\Users\Index as UsersIndex;

use App\Livewire\Admin\Reports\Index as ReportsIndex;
use App\Livewire\Admin\Reports\Payments as ReportsPayments;
use App\Livewire\Admin\Reports\Attendance as ReportsAttendance;

// PEOPLE
Route::get('people', PeopleIndex::class)->name('people.index');
Route::get('people/{person}/edit', PeopleEdit::class)->name('people.edit');
Route::get('people/{person}/clinical', PeopleClinical::class)->name('people.clinical');
Route::get('people/{person}', PeopleShow::class)->name('people.show');

// ADMISIÓN
Route::get('admission/create', AdmissionWizard::class)->name('admission.create');

// AGENDA unificada
Route::get('agenda', AgendaWeekly::class)->name('agenda.index');

// GYM
Route::get('plans', PlansIndex::class)->name('plans.index');
Route::get('plans/create', PlansCreate::class)->name('plans.create');
Route::get('plans/{plan}/edit', PlansEdit::class)->name('plans.edit');
Route::get('plans/{plan}', PlansShow::class)->name('plans.show');

Route::get('subscriptions', SubscriptionsIndex::class)->name('subscriptions.index');
Route::get('subscriptions/create', SubscriptionsCreate::class)->name('subscriptions.create');

Route::get('payments', PaymentsIndex::class)->name('payments.index');
Route::get('payments/create', PaymentsCreate::class)->name('payments.create');

// KINESIOLOGÍA
Route::prefix('kine')->name('kine.')->group(function () {
    Route::get('patients', KinePatientsIndex::class)->name('patients.index');
    Route::get('patients/{profile}', KinePatientsShow::class)->name('patients.show');

    Route::get('tipos-tratamientos', KineTiposIndex::class)->name('tipos-tratamientos.index');
    Route::get('tipos-tratamientos/create', KineTiposCreate::class)->name('tipos-tratamientos.create');
    Route::get('tipos-tratamientos/{tipoTratamiento}/edit', KineTiposEdit::class)->name('tipos-tratamientos.edit');
    Route::get('protocols/{tipo}/apply', KineProtocolApply::class)->name('protocols.apply');
    Route::get('sessions/{appointment}/attend', KineSessionAttend::class)->name('sessions.attend');

    Route::get('treatments', KineTreatmentsIndex::class)->name('treatments.index');
    Route::get('treatments/create', KineTreatmentsForm::class)->name('treatments.create');
    Route::get('treatments/{treatment}/edit', KineTreatmentsForm::class)->name('treatments.edit');

    Route::get('appointments', KineAppointmentsIndex::class)->name('appointments.index');
    Route::get('appointments/create', KineAppointmentsForm::class)->name('appointments.create');
    Route::get('appointments/{appointment}/edit', KineAppointmentsForm::class)->name('appointments.edit');

    Route::get('payments', KinePaymentsIndex::class)->name('payments.index');
    Route::get('payments/create', KinePaymentsCreate::class)->name('payments.create');
});

// ESTÉTICA
Route::prefix('estetic')->name('estetic.')->group(function () {
    Route::get('patients', EsteticPatientsIndex::class)->name('patients.index');
    Route::get('patients/{profile}', EsteticPatientsShow::class)->name('patients.show');

    Route::get('tipos-tratamientos', EsteticTiposIndex::class)->name('tipos-tratamientos.index');
    Route::get('tipos-tratamientos/create', EsteticTiposCreate::class)->name('tipos-tratamientos.create');
    Route::get('tipos-tratamientos/{tipoTratamiento}/edit', EsteticTiposEdit::class)->name('tipos-tratamientos.edit');
    Route::get('protocols/{tipo}/apply', EsteticProtocolApply::class)->name('protocols.apply');
    Route::get('sessions/{appointment}/attend', EsteticSessionAttend::class)->name('sessions.attend');

    Route::redirect('treatments', '/admin/estetic/patients')->name('treatments.index');
    Route::get('treatments/{treatment}/edit', EsteticTreatmentsForm::class)->name('treatments.edit');

    Route::get('appointments', EsteticAppointmentsIndex::class)->name('appointments.index');
    Route::get('appointments/create', EsteticAppointmentsForm::class)->name('appointments.create');
    Route::get('appointments/{appointment}/edit', EsteticAppointmentsForm::class)->name('appointments.edit');

    Route::get('payments', EsteticPaymentsIndex::class)->name('payments.index');
    Route::get('payments/create', EsteticPaymentsCreate::class)->name('payments.create');
});

// CAJA CONSOLIDADA
Route::get('cash/daily', CashDaily::class)->name('cash.daily');

// USUARIOS DEL SISTEMA
Route::get('users', UsersIndex::class)->name('users.index');

// WHATSAPP
Route::get('whatsapp/templates', WhatsappTemplates::class)->name('whatsapp.templates');

// REPORTES
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', ReportsIndex::class)->name('index');
    Route::get('payments', ReportsPayments::class)->name('payments');
    Route::get('attendance', ReportsAttendance::class)->name('attendance');
});
