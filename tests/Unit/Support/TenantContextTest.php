<?php

namespace Tests\Unit\Support;

use App\Models\Condominium;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantContext::clear();

        parent::tearDown();
    }

    public function test_clear_removes_static_and_container_bindings(): void
    {
        $condominium = Condominium::factory()->create();

        TenantContext::set($condominium);

        $this->assertTrue(TenantContext::isSet());
        $this->assertTrue(app()->bound('currentCondominium'));
        $this->assertTrue(app()->bound(TenantContext::class));

        TenantContext::clear();

        $this->assertFalse(TenantContext::isSet());
        $this->assertFalse(app()->bound('currentCondominium'));
        $this->assertFalse(app()->bound(TenantContext::class));
    }
}
