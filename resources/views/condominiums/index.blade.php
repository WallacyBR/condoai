@extends('layouts.app')

@section('title', 'Condomínios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Condomínios</h2>
    <a href="{{ route('condominiums.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Condomínio
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cidade</th>
                        <th>Estado</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($condominiums as $condominium)
                        <tr>
                            <td>{{ $condominium->name }}</td>
                            <td>{{ $condominium->city }}</td>
                            <td>{{ $condominium->state }}</td>
                            <td>{{ $condominium->responsible }}</td>
                            <td>
                                <span class="badge bg-{{ $condominium->active ? 'success' : 'danger' }}">
                                    {{ $condominium->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('condominiums.edit', $condominium) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $condominium->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $condominium->id }}" 
                                          action="{{ route('condominiums.destroy', $condominium) }}" 
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
            {{ $condominiums->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este condomínio?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush 