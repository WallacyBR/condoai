<?php

namespace Tests\Unit;

use App\Http\Middleware\TenantMiddleware;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    public function test_resolve_subdomain(): void
    {
        $condo = Condominium::factory()->create(['domain' => 'alpha']);
        $request = Request::create('/','GET',[],[],[],['HTTP_HOST' => 'alpha.'.env('TENANT_BASE_DOMAIN')]);
        $middleware = new TenantMiddleware();
        $middleware->handle($request, function ($req) {});

        $this->assertTrue(app()->bound('currentCondominium'));
        $this->assertEquals($condo->id, app('currentCondominium')->id);
    }
}
