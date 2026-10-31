<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Doctor = 'doctor';
    case Staff = 'staff';

    /**
     * Get the human readable name of the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => __('Owner'),
            self::Doctor => __('Doctor'),
            self::Staff => __('Staff'),
        };
    }
}
