<?php

namespace App\Livewire\Admin\Whatsapp;

use App\Models\WhatsappTemplate;
use Livewire\Component;

class Templates extends Component
{
    public array $forms = [];

    public function mount(): void
    {
        foreach (WhatsappTemplate::orderBy('id')->get() as $t) {
            $this->forms[$t->id] = [
                'name'   => $t->name,
                'body'   => $t->body,
                'active' => $t->active,
            ];
        }
    }

    public function save(int $id): void
    {
        $data = $this->forms[$id] ?? null;
        if (!$data) return;

        $t = WhatsappTemplate::findOrFail($id);
        $t->update([
            'name'   => $data['name'],
            'body'   => $data['body'],
            'active' => (bool) $data['active'],
        ]);

        session()->flash('saved-' . $id, 'Plantilla guardada.');
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.templates', [
            'templates' => WhatsappTemplate::orderBy('id')->get(),
        ]);
    }
}
