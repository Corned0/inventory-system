<?php

namespace App\Enums;

enum PutAwayStatus: string
{
    case Draft = 'draft';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}