<?php

namespace App\Enums;

enum UserRole: string
{
    case Instructor = 'instructor';
    case Student    = 'student';
}
