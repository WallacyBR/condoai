@extends('layouts.app')

@section('title', 'Editar Torre')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editar Torre</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('towers.update', $tower) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="condominium_id" class="form-label">Condomínio</label>
                            <select name="condominium_id" id="condominium_id" 
                                    class="form-select @error('condominium_id') is-invalid @enderror" required>
                                <option value="">Selecione um condomínio</option>
                                @foreach($condominiums as $id => $name)
                                    <option value="{{ $id }}" 
                                        {{ old('condominium_id', $tower->condominium_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condominium_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $tower->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="active" 
                                       name="active" value="1" 
                                       {{ old('active', $tower->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Ativa</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('towers.show', $tower) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 