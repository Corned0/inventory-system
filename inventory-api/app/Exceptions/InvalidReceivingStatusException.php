<?php

namespace App\Exceptions;

use App\Enums\ReceivingStatus;

class InvalidReceivingStatusException extends BusinessException
{
    public function __construct(
        ReceivingStatus $current,
        ReceivingStatus $expected,
    ) {
        parent::__construct(
            message: "Receiving cannot be processed while it is {$current->value}.",
            status: 409,
            errors: [
                'status' => [
                    "Expected status: {$expected->value}.",
                ],
            ],
        );
    }
}