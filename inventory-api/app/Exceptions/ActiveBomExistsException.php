<?php

namespace App\Exceptions;

use App\Exceptions\BusinessException;

class ActiveBomExistsException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The item already has an active BOM.',
            status: 409,
        );
    }
}