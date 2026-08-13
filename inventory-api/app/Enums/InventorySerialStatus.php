<?php

namespace App\Enums;

enum InventorySerialStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Issued = 'issued';
    case Installed = 'installed';
    case Repair = 'repair';
    case Damaged = 'damaged';
    case Lost = 'lost';
    case Disposed = 'disposed';
}