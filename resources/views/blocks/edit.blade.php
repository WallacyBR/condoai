@extends('layouts.app')

@section('title', 'Editar Bloco')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editar Bloco</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('blocks.update', $block) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="tower_id" class="form-label">Torre</label>
                            <select name="tower_id" id="tower_id" 
                                    class="form-select @error('tower_id') is-invalid @enderror" required>
                                <option value="">Selecione uma torre</option>
                                @foreach($towers as $id => $name)
                                    <option value="{{ $id }}" 
                                        {{ old('tower_id', $block->tower_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tower_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $block->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="active" 
                                       name="active" value="1" 
                                       {{ old('active', $block->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Ativo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('blocks.show', $block) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 