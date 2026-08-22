<?php

namespace App\Enums;

enum AlertChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
}
