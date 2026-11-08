<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Moradores Recentes</h5>
        <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Novo
        </a>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            @forelse($residents as $resident)
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">{{ $resident->name }}</h6>
                        <small class="text-muted">
                            {{ $resident->condominium->name }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('residents.show', $resident) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button wire:click="toggleActive({{ $resident->id }})" class="btn btn-sm {{ $resident->active ? 'btn-success' : 'btn-danger' }}">
                            <i class="fas fa-{{ $resident->active ? 'check' : 'times' }}"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted">
                    Nenhum morador cadastrado
                </div>
            @endforelse
        </div>
    </div>
</div> 