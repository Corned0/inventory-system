<?php

namespace App\Exceptions;

use App\Exceptions\BusinessException;

class InvalidReceivingForPutAwayException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The receiving is not eligible for put-away.',
            status: 409,
            errors: [
                'receiving' => [
                    'The receiving must be accepted or partially accepted '
                    .'before put-away can be created.',
                ],
            ],
        );
    }
}