<?php

namespace App\Exceptions;

class InventorySerialItemChangeException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The serial item cannot be changed after it has been referenced by inventory records.',
            status: 422,
        );
    }
}
