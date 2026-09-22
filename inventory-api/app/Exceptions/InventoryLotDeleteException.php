<?php

namespace App\Exceptions;

class InventoryLotDeleteException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The lot cannot be deleted because it is referenced by inventory records.',
            status: 409,
        );
    }
}
