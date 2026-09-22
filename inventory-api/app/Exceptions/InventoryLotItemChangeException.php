<?php

namespace App\Exceptions;

class InventoryLotItemChangeException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The lot item cannot be changed after it has been referenced by inventory records.',
            status: 422,
        );
    }
}
