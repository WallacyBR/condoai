<?php

namespace Tests\Unit;

use Tests\TestCase;

class JwtConfigurationTest extends TestCase
{
    /** @test */
    public function arquivo_de_configuracao_jwt_esta_disponivel_e_secret_definido(): void
    {
        $this->assertNotEmpty(config('jwt'));
        $this->assertNotEmpty(config('jwt.secret'));
    }
}
