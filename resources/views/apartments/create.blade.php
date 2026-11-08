@extends('layouts.app')

@section('title', 'Novo Apartamento')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Novo Apartamento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('apartments.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="block_id" class="form-label">Bloco</label>
                            <select name="block_id" id="block_id" class="form-select @error('block_id') is-invalid @enderror" required>
                                <option value="">Selecione um bloco</option>
                                @foreach($blocks as $id => $name)
                                    <option value="{{ $id }}" {{ old('block_id', $selectedBlock) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('block_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="number" class="form-label">Número</label>
                            <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number') }}" required>
                            @error('number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="floor" class="form-label">Andar</label>
                            <input type="number" class="form-control @error('floor') is-invalid @enderror" id="floor" name="floor" value="{{ old('floor') }}" required>
                            @error('floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="owner_id" class="form-label">Proprietário</label>
                            <select name="owner_id" id="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                <option value="">--</option>
                                @foreach($residents as $id => $name)
                                    <option value="{{ $id }}" {{ old('owner_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="tenant_id" class="form-label">Inquilino</label>
                            <select name="tenant_id" id="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror">
                                <option value="">--</option>
                                @foreach($residents as $id => $name)
                                    <option value="{{ $id }}" {{ old('tenant_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Ativo</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('apartments.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
