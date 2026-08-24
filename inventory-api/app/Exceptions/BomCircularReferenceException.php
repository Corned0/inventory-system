<?php

namespace App\Exceptions;

use App\Exceptions\BusinessException;

class BomCircularReferenceException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'An item cannot be a component of its own BOM.',
            status: 422,
        );
    }
}