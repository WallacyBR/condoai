@extends('layouts.app')

@section('title', 'Admin Vue')

@section('content')
<div id="app" class="container mt-4">
    <h2>Gerenciamento</h2>
    <profile-manager></profile-manager>
    <user-manager></user-manager>
    <condominium-manager></condominium-manager>
    <unit-manager></unit-manager>
    <resident-manager></resident-manager>
</div>
@endsection
