<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Sales = 'sales';
    case Manager = 'manager';
    case Finance = 'finance';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Sales => 'Sales',
            self::Manager => 'Sales Manager',
            self::Finance => 'Finance',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
