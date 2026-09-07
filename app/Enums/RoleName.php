<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'Admin';
    case Manager = 'Manager';
    case Staff = 'Staff';
    case Viewer = 'Viewer';
}
