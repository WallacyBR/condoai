<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Condomínios Recentes</h5>
        <a href="{{ route('condominiums.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Novo
        </a>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            @forelse($condominiums as $condominium)
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">{{ $condominium->name }}</h6>
                        <small class="text-muted">
                            {{ $condominium->city }}/{{ $condominium->state }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button wire:click="toggleActive({{ $condominium->id }})" class="btn btn-sm {{ $condominium->active ? 'btn-success' : 'btn-danger' }}">
                            <i class="fas fa-{{ $condominium->active ? 'check' : 'times' }}"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted">
                    Nenhum condomínio cadastrado
                </div>
            @endforelse
        </div>
    </div>
</div> 