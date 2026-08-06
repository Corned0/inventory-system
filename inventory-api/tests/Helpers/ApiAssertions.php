<?php

namespace Tests\Helpers;

use Illuminate\Testing\TestResponse;

class ApiAssertions
{
    public static function created(
        TestResponse $response,
        string $message
    ): void {
        $response
            ->assertCreated()
            ->assertJsonFragment([
                'message' => $message,
            ]);
    }

    public static function updated(
        TestResponse $response,
        string $message
    ): void {
        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => $message,
            ]);
    }

    public static function deleted(
        TestResponse $response,
        string $message
    ): void {
        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => $message,
            ]);
    }
}