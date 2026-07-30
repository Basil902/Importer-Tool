<?php

namespace App\Enum;

enum ImportStatusEnum: string
{
    case STATUS_UPLOADED = 'uploaded';
    case STATUS_PROCESSING = 'processing';
    case STATUS_PROCESSED = 'processed';
    case STATUS_ERROR = 'error';
}