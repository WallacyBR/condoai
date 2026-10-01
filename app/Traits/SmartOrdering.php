<?php

namespace App\Traits;

trait SmartOrdering
{
    /**
     * Scope para ordenação inteligente por nome
     * Ordena números numericamente e letras alfabeticamente
     */
    public function scopeOrderByNameSmart($query)
    {
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return $query->orderBy('name');
        }

        return $query->orderByRaw("
            CASE 
                WHEN name REGEXP '^[0-9]+$' THEN 0 
                ELSE 1 
            END,
            CASE 
                WHEN name REGEXP '^[0-9]+$' THEN CAST(name AS UNSIGNED) 
                ELSE name 
            END
        ");
    }

    /**
     * Scope para ordenação inteligente por qualquer campo
     * Ordena números numericamente e letras alfabeticamente
     */
    public function scopeOrderByFieldSmart($query, $field)
    {
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return $query->orderBy($field);
        }

        return $query->orderByRaw("
            CASE 
                WHEN {$field} REGEXP '^[0-9]+$' THEN 0 
                ELSE 1 
            END,
            CASE 
                WHEN {$field} REGEXP '^[0-9]+$' THEN CAST({$field} AS UNSIGNED) 
                ELSE {$field} 
            END
        ");
    }
}
