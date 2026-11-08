<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CondominiumScope implements Scope
{
    public function __construct(private ?string $relation = null)
    {
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (!app()->bound('currentCondominium')) {
            return;
        }

        $condominium = app('currentCondominium');

        if ($this->relation) {
            $builder->whereHas($this->relation, function (Builder $q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            });
        } else {
            $builder->where($model->getTable() . '.condominium_id', $condominium->id);
        }
    }
}
