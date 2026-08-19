<?php

namespace App\Exceptions;

use App\Enums\ReceivingStatus;

class InvalidReceivingStatusExceptionArrayValue extends BusinessException
{
    /**
     * @param array<int, ReceivingStatus> $expected
     */
    public function __construct(
        ReceivingStatus $current,
        array $expected,
    ) {
        $expectedValues = array_map(
            static fn (ReceivingStatus $status): string => $status->value,
            $expected,
        );

        parent::__construct(
            message: sprintf(
                'Receiving must be in one of these statuses: %s.',
                implode(', ', $expectedValues),
            ),
            status: 409,
            errors: [
                'status' => [
                    "Current status: {$current->value}.",
                    'Expected status: '.implode(', ', $expectedValues).'.',
                ],
            ],
        );
    }
}