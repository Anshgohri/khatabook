<?php

namespace App\Enums;

enum RoleName: string
{
    case SystemAdmin = 'System Admin';
    case Admin = 'Admin';
    case Manager = 'Manager';
    case Staff = 'Staff';
    case Viewer = 'Viewer';
    case Employee = 'ROLE_EMPLOYEE';
    case Financier = 'ROLE_FINANCIERS';
    case Customer = 'ROLE_CUSTOMER';
}
