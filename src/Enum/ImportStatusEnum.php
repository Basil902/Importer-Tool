<?php

namespace App\Enum;

enum ImportStatusEnum: string
{
    case STATUS_PROCESSED = 'processed';
    case STATUS_PENDING = 'pending';
    case STATUS_ERROR = 'error';
}