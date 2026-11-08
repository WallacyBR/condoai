@extends('layouts.app')

@section('title', 'Torres')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Torres</h2>
    <a href="{{ route('towers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Torre
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('towers.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="condominium_id">Condomínio</label>
                        <select name="condominium_id" id="condominium_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($condominiums as $id => $name)
                                <option value="{{ $id }}" {{ request('condominium_id') == $id ? 'selected' : '' }}>
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
                        <th>Condomínio</th>
                        <th>Blocos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($towers as $tower)
                        <tr>
                            <td>{{ $tower->name }}</td>
                            <td>{{ $tower->condominium->name }}</td>
                            <td>{{ $tower->blocks_count ?? 0 }}</td>
                            <td>
                                <span class="badge bg-{{ $tower->active ? 'success' : 'danger' }}">
                                    {{ $tower->active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('towers.show', $tower) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('towers.edit', $tower) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $tower->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $tower->id }}" 
                                          action="{{ route('towers.destroy', $tower) }}" 
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
            {{ $towers->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir esta torre?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush 