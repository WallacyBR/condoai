<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Resident;
use Livewire\Component;

class ResidentCard extends Component
{
    public $count;

    protected $listeners = ['residentUpdated' => 'updateCount'];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $this->count = Resident::count();
    }

    public function render()
    {
        return view('livewire.dashboard.resident-card');
    }
} 