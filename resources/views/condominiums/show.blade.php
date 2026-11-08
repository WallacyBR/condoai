@extends('layouts.app')

@section('title', $condominium->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $condominium->name }}</h2>
    <div>
        <a href="{{ route('condominiums.edit', $condominium) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('condominiums.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações Gerais</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">CNPJ</dt>
                    <dd class="col-sm-8">{{ $condominium->document }}</dd>

                    <dt class="col-sm-4">Endereço</dt>
                    <dd class="col-sm-8">{{ $condominium->address }}</dd>

                    <dt class="col-sm-4">Cidade/Estado</dt>
                    <dd class="col-sm-8">{{ $condominium->city }}/{{ $condominium->state }}</dd>

                    <dt class="col-sm-4">CEP</dt>
                    <dd class="col-sm-8">{{ $condominium->zip_code }}</dd>

                    <dt class="col-sm-4">Telefone</dt>
                    <dd class="col-sm-8">{{ $condominium->phone }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $condominium->email }}</dd>

                    <dt class="col-sm-4">Responsável</dt>
                    <dd class="col-sm-8">{{ $condominium->responsible }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $condominium->active ? 'success' : 'danger' }}">
                            {{ $condominium->active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Torres</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newTowerModal">
                    <i class="fas fa-plus"></i> Nova Torre
                </button>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse($condominium->towers as $tower)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $tower->name }}</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse" 
                                            data-bs-target="#tower{{ $tower->id }}Blocks">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editTower({{ $tower->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="collapse mt-2" id="tower{{ $tower->id }}Blocks">
                                <div class="list-group">
                                    @foreach($tower->blocks as $block)
                                        <div class="list-group-item">
                                            {{ $block->name }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Nenhuma torre cadastrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Blocos</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newBlockModal">
                    <i class="fas fa-plus"></i> Novo Bloco
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Torre</th>
                                <th>Apartamentos</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($condominium->blocks as $block)
                                <tr>
                                    <td>{{ $block->name }}</td>
                                    <td>{{ $block->tower->name ?? 'N/A' }}</td>
                                    <td>{{ $block->apartments_count ?? 0 }}</td>
                                    <td>
                                        <span class="badge bg-{{ $block->active ? 'success' : 'danger' }}">
                                            {{ $block->active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editBlock({{ $block->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="{{ route('blocks.show', $block) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum bloco cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Torre -->
<div class="modal fade" id="newTowerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('towers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="condominium_id" value="{{ $condominium->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Nova Torre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Novo Bloco -->
<div class="modal fade" id="newBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blocks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="condominium_id" value="{{ $condominium->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Novo Bloco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="tower_id" class="form-label">Torre (opcional)</label>
                        <select class="form-select" id="tower_id" name="tower_id">
                            <option value="">Selecione uma torre</option>
                            @foreach($condominium->towers as $tower)
                                <option value="{{ $tower->id }}">{{ $tower->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editTower(id) {
    // Implementar edição de torre
}

function editBlock(id) {
    // Implementar edição de bloco
}
</script>
@endpush 