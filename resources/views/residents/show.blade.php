@extends('layouts.app')

@section('title', 'Detalhes do Morador')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $resident->name }}</h2>
        <div class="btn-group">
            <a href="{{ route('residents.edit', $resident) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Excluir
            </button>
            <form id="delete-form" action="{{ route('residents.destroy', $resident) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informações</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Condomínio</dt>
                <dd class="col-sm-8">{{ $resident->condominium->name }}</dd>

                <dt class="col-sm-4">Telefone</dt>
                <dd class="col-sm-8">{{ $resident->phone }}</dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $resident->email }}</dd>

                <dt class="col-sm-4">Status</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $resident->active ? 'success' : 'danger' }}">
                        {{ $resident->active ? 'Ativo' : 'Inativo' }}
                    </span>
                </dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Apartamentos</h5>
        </div>
        <div class="card-body">
            @if($resident->residingApartments->isEmpty())
                <p class="text-muted mb-0">Nenhum apartamento vinculado.</p>
            @else
                <ul class="list-group">
                    @foreach($resident->residingApartments as $apartment)
                        <li class="list-group-item">
                            {{ $apartment->number }} - {{ $apartment->block->name }} ({{ $apartment->block->tower->name }})
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir este morador?')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
