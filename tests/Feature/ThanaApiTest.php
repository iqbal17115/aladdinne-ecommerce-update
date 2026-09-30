<?php

namespace Tests\Feature;

use App\Http\Controllers\API\CountryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ThanaApiTest extends TestCase
{
    public function test_index_thanas_returns_empty_list_when_table_is_missing(): void
    {
        Schema::shouldReceive('hasTable')
            ->with('thanas')
            ->andReturn(false);

        $controller = new CountryController();
        $response = $controller->indexThanas();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $response->getData(true)['data']['thanas']);
    }
}
