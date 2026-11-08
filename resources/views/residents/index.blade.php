@extends('layouts.app')

@section('title', 'Moradores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Moradores</h2>
    <a href="{{ route('residents.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Morador
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('residents.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="condominium_id" class="form-label">Condomínio</label>
                    <select name="condominium_id" id="condominium_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($condominiums as $id => $name)
                            <option value="{{ $id }}" {{ request('condominium_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
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
                        <th>Nome</th>
                        <th>Condomínio</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($residents as $resident)
                        <tr>
                            <td>{{ $resident->name }}</td>
                            <td>{{ $resident->condominium->name }}</td>
                            <td>{{ $resident->phone }}</td>
                            <td>{{ $resident->email }}</td>
                            <td>
                                <span class="badge bg-{{ $resident->active ? 'success' : 'danger' }}">
                                    {{ $resident->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('residents.show', $resident) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $resident->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $resident->id }}" action="{{ route('residents.destroy', $resident) }}" method="POST" style="display:none;">
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
            {{ $residents->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este morador?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
