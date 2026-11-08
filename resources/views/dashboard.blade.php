@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <h2>Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <livewire:counter />
    </div>

    <div class="col-md-4 mb-4">
        <livewire:dashboard.condominium-card />
    </div>

    <div class="col-md-4 mb-4">
        <livewire:dashboard.resident-card />
    </div>

    <div class="col-md-4 mb-4">
        <livewire:dashboard.apartment-card />
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <livewire:dashboard.recent-condominiums />
    </div>

    <div class="col-md-6 mb-4">
        <livewire:dashboard.recent-residents />
    </div>
</div>
@endsection 