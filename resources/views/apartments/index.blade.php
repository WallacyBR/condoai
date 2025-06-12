@extends('layouts.app')

@section('title', 'Apartamentos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Apartamentos</h2>
    <a href="{{ route('apartments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Apartamento
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('apartments.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="block_id" class="form-label">Bloco</label>
                    <select name="block_id" id="block_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($blocks as $id => $name)
                            <option value="{{ $id }}" {{ request('block_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
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
                        <th>Número</th>
                        <th>Bloco</th>
                        <th>Torre</th>
                        <th>Moradores</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apartments as $apartment)
                        <tr>
                            <td>{{ $apartment->number }}</td>
                            <td>{{ $apartment->block->name }}</td>
                            <td>{{ $apartment->block->tower->name }}</td>
                            <td>{{ $apartment->residents_count ?? 0 }}</td>
                            <td>
                                <span class="badge bg-{{ $apartment->active ? 'success' : 'danger' }}">
                                    {{ $apartment->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('apartments.show', $apartment) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('apartments.edit', $apartment) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $apartment->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $apartment->id }}" action="{{ route('apartments.destroy', $apartment) }}" method="POST" style="display:none;">
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
            {{ $apartments->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este apartamento?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
