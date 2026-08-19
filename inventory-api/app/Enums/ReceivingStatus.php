<?php

namespace App\Enums;

enum ReceivingStatus: string
{
    case Draft = 'draft';
    case Received = 'received';
    case UnderInspection = 'under_inspection';
    case Accepted = 'accepted';
    case PartiallyAccepted = 'partially_accepted';
    case Rejected = 'rejected';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}