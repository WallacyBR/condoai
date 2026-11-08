<?php

namespace App\Support;

use App\Models\Condominium;
use RuntimeException;

class TenantContext
{
    private static ?Condominium $condominium = null;

    public static function set(Condominium $condominium): void
    {
        self::$condominium = $condominium;
        app()->instance('currentCondominium', $condominium);
        app()->instance(self::class, $condominium);
    }

    public static function get(): Condominium
    {
        if (self::$condominium !== null) {
            return self::$condominium;
        }

        if (app()->bound('currentCondominium')) {
            return app('currentCondominium');
        }

        throw new RuntimeException('Contexto de tenant não definido.');
    }

    public static function id(): int
    {
        return self::get()->id;
    }

    public static function isSet(): bool
    {
        return self::$condominium !== null || app()->bound('currentCondominium');
    }

    public static function clear(): void
    {
        self::$condominium = null;
    }
}
