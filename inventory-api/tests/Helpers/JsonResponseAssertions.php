<?php

namespace Tests\Helpers;

use Illuminate\Testing\TestResponse;

class JsonResponseAssertions
{
    public static function pagination(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
    }
}