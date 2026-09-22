<?php

namespace App\Exceptions;

class InventoryLotDateRangeException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The expiration date cannot be before the manufactured date.',
            status: 422,
        );
    }
}
