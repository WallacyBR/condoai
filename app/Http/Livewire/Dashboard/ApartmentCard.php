<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Apartment;
use Livewire\Component;

class ApartmentCard extends Component
{
    public $count;

    public function mount()
    {
        $this->count = Apartment::count();
    }

    public function render()
    {
        return view('livewire.dashboard.apartment-card');
    }
} 