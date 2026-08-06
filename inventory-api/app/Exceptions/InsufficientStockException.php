<?php

namespace App\Exceptions;

class InsufficientStockException extends BusinessException
{
    public function __construct(
        public readonly int $available,
        public readonly int $requested,
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