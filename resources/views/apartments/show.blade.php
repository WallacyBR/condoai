@extends('layouts.app')

@section('title', 'Detalhes do Apartamento')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Apartamento {{ $apartment->number }}</h2>
        <div class="btn-group">
            <a href="{{ route('apartments.edit', $apartment) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Excluir
            </button>
            <form id="delete-form" action="{{ route('apartments.destroy', $apartment) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Bloco</dt>
                        <dd class="col-sm-8">{{ $apartment->block->name }}</dd>

                        <dt class="col-sm-4">Torre</dt>
                        <dd class="col-sm-8">{{ $apartment->block->tower->name }}</dd>

                        <dt class="col-sm-4">Andar</dt>
                        <dd class="col-sm-8">{{ $apartment->floor }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $apartment->active ? 'success' : 'danger' }}">
                                {{ $apartment->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Proprietário/Inquilino</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Proprietário</dt>
                        <dd class="col-sm-8">{{ $apartment->owner->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Inquilino</dt>
                        <dd class="col-sm-8">{{ $apartment->tenant->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Moradores</h5>
                </div>
                <div class="card-body">
                    @if($apartment->residents->isEmpty())
                        <p class="text-muted mb-0">Nenhum morador cadastrado.</p>
                    @else
                        <ul class="list-group">
                            @foreach($apartment->residents as $resident)
                                <li class="list-group-item">{{ $resident->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir este apartamento?')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
