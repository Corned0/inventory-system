<?php

namespace App\Exceptions;

class InventorySerialDeleteException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The serial cannot be deleted because it is referenced by inventory records.',
            status: 409,
        );
    }
}
