<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Resident;
use Livewire\Component;

class RecentResidents extends Component
{
    public function toggleActive($id)
    {
        $resident = Resident::findOrFail($id);
        $resident->update(['active' => !$resident->active]);
        $this->emit('residentUpdated');
    }

    public function render()
    {
        return view('livewire.dashboard.recent-residents', [
            'residents' => Resident::with('condominium')
                ->latest()
                ->take(5)
                ->get()
        ]);
    }
} 