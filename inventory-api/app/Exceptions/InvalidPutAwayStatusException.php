<?php

namespace App\Exceptions;

use App\Exceptions\BusinessException;
use App\Enums\PutAwayStatus;

class InvalidPutAwayStatusException extends BusinessException
{
    public function __construct(
        public readonly PutAwayStatus $current,
        public readonly array $expected,
    ) {
        parent::__construct(
            message: 'Invalid put-away status.',
            status: 409,
            errors: [
                'status' => [
                    'The put-away cannot perform this operation '
                    .'from its current status.',
                ],
            ],
        );
    }
}