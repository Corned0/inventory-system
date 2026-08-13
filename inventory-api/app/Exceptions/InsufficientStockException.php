<?php

namespace App\Exceptions;

class InsufficientStockException extends BusinessException
{
    public function __construct(
        public readonly string|float $available,
        public readonly string|float $requested,
    ) {
        parent::__construct(
            message: 'Insufficient stock.',
            status: 409,
            errors: [
                'quantity' => [
                    "Only {$available} units are available.",
                ],
            ],
        );
    }
}