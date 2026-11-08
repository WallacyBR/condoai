<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Condominium;
use Livewire\Component;

class RecentCondominiums extends Component
{
    public function toggleActive($id)
    {
        $condominium = Condominium::findOrFail($id);
        $condominium->update(['active' => !$condominium->active]);
        $this->emit('condominiumUpdated');
    }

    public function render()
    {
        return view('livewire.dashboard.recent-condominiums', [
            'condominiums' => Condominium::latest()->take(5)->get()
        ]);
    }
} 