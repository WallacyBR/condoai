@extends('layouts.app')

@section('title', 'Blocos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Blocos</h2>
    <a href="{{ route('blocks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Bloco
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('blocks.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tower_id">Torre</label>
                        <select name="tower_id" id="tower_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach($towers as $id => $name)
                                <option value="{{ $id }}" {{ request('tower_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Torre</th>
                        <th>Condomínio</th>
                        <th>Apartamentos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blocks as $block)
                        <tr>
                            <td>{{ $block->name }}</td>
                            <td>{{ $block->tower->name }}</td>
                            <td>{{ $block->tower->condominium->name }}</td>
                            <td>{{ $block->apartments_count ?? 0 }}</td>
                            <td>
                                <span class="badge bg-{{ $block->active ? 'success' : 'danger' }}">
                                    {{ $block->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('blocks.show', $block) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('blocks.edit', $block) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $block->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $block->id }}" 
                                          action="{{ route('blocks.destroy', $block) }}" 
                                          method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $blocks->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este bloco?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush 