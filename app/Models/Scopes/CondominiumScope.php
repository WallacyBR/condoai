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
        $condominium = tenant();

        if ($condominium === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        if ($this->relation) {
            $builder->whereHas($this->relation, function (Builder $query) use ($condominium) {
                $query->where('condominium_id', $condominium->id);
            });

            return;
        }

        $builder->where($model->getTable().'.condominium_id', $condominium->id);
    }
}
