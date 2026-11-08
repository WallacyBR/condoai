<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Condominium;
use Livewire\Component;

class CondominiumCard extends Component
{
    public $count;

    protected $listeners = ['condominiumUpdated' => 'updateCount'];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $this->count = Condominium::count();
    }

    public function render()
    {
        return view('livewire.dashboard.condominium-card');
    }
} 