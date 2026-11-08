<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CondominiumScope implements Scope
{
    public function __construct(private ?string $relation = null) {}

    public function apply(Builder $builder, Model $model): void
    {
        // Log para debug
        \Log::info('CondominiumScope::apply - Debug info', [
            'model' => get_class($model),
            'app_bound_currentCondominium' => app()->bound('currentCondominium'),
            'relation' => $this->relation
        ]);
        
        if (! app()->bound('currentCondominium')) {
            \Log::warning('CondominiumScope::apply - currentCondominium não está disponível');
            return;
        }

        $condominium = app('currentCondominium');
        
        \Log::info('CondominiumScope::apply - Aplicando filtro', [
            'condominium_id' => $condominium->id,
            'relation' => $this->relation
        ]);

        if ($this->relation) {
            $builder->whereHas($this->relation, function (Builder $q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            });
        } else {
            $builder->where($model->getTable().'.condominium_id', $condominium->id);
        }
    }
}
