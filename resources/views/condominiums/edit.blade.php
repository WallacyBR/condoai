@extends('layouts.app')

@section('title', 'Editar Condomínio')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Editar Condomínio</h2>
    <div>
        <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-info">
            <i class="fas fa-eye"></i> Visualizar
        </a>
        <a href="{{ route('condominiums.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('condominiums.update', $condominium) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nome do Condomínio</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $condominium->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="document" class="form-label">CNPJ</label>
                    <input type="text" class="form-control @error('document') is-invalid @enderror" 
                           id="document" name="document" value="{{ old('document', $condominium->document) }}" required>
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-8 mb-3">
                    <label for="address" class="form-label">Endereço</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                           id="address" name="address" value="{{ old('address', $condominium->address) }}" required>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="zip_code" class="form-label">CEP</label>
                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                           id="zip_code" name="zip_code" value="{{ old('zip_code', $condominium->zip_code) }}" required>
                    @error('zip_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="city" class="form-label">Cidade</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                           id="city" name="city" value="{{ old('city', $condominium->city) }}" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="state" class="form-label">Estado</label>
                    <select class="form-select @error('state') is-invalid @enderror" 
                            id="state" name="state" required>
                        <option value="">Selecione...</option>
                        @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                            <option value="{{ $state }}" {{ old('state', $condominium->state) == $state ? 'selected' : '' }}>
                                {{ $state }}
                            </option>
                        @endforeach
                    </select>
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone', $condominium->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $condominium->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="responsible" class="form-label">Responsável</label>
                    <input type="text" class="form-control @error('responsible') is-invalid @enderror" 
                           id="responsible" name="responsible" value="{{ old('responsible', $condominium->responsible) }}" required>
                    @error('responsible')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="domain" class="form-label">Domínio</label>
                    <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                           id="domain" name="domain" value="{{ old('domain', $condominium->domain) }}" required>
                    <small class="form-text text-muted">
                        Identificador único para o condomínio (usado para acesso ao sistema)
                    </small>
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input @error('active') is-invalid @enderror" 
                               id="active" name="active" value="1" 
                               {{ old('active', $condominium->active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Ativo</label>
                        @error('active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    $('#document').mask('00.000.000/0000-00');
    $('#phone').mask('(00) 00000-0000');
    $('#zip_code').mask('00000-000');
});
</script>
@endpush 