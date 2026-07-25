<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Pending    = 'pending';
    case Active     = 'active';
    case Submitted  = 'submitted';
    case Terminated = 'terminated';
}
