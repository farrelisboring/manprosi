<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Staff = 'staff';
    case Manager = 'manager';
    case Nurse = 'nurse';
}
