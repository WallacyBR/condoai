<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /** @test */
    public function verifica_driver_sqlite_nos_testes(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
    }
}
