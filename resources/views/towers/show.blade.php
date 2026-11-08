@extends('layouts.app')

@section('title', 'Detalhes da Torre')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $tower->name }}</h2>
        <div class="btn-group">
            <a href="{{ route('towers.edit', $tower) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Excluir
            </button>
            <form id="delete-form" action="{{ route('towers.destroy', $tower) }}" 
                  method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações da Torre</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Condomínio</dt>
                        <dd class="col-sm-8">{{ $tower->condominium->name }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $tower->active ? 'success' : 'danger' }}">
                                {{ $tower->active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Blocos</dt>
                        <dd class="col-sm-8">{{ $tower->blocks->count() }}</dd>

                        <dt class="col-sm-4">Apartamentos</dt>
                        <dd class="col-sm-8">
                            {{ $tower->blocks->sum(function($block) { 
                                return $block->apartments->count(); 
                            }) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Blocos</h5>
                    <a href="{{ route('blocks.create', ['tower_id' => $tower->id]) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Bloco
                    </a>
                </div>
                <div class="card-body">
                    @if($tower->blocks->isEmpty())
                        <p class="text-muted text-center mb-0">Nenhum bloco cadastrado.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Apartamentos</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tower->blocks as $block)
                                        <tr>
                                            <td>{{ $block->name }}</td>
                                            <td>{{ $block->apartments->count() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $block->active ? 'success' : 'danger' }}">
                                                    {{ $block->active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('blocks.show', $block) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('blocks.edit', $block) }}" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
    if (confirm('Tem certeza que deseja excluir esta torre?')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush 