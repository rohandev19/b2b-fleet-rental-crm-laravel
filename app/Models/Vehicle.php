<?php

namespace App\Models;

use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    public const TYPES = ['mpv', 'suv', 'sedan', 'commercial', 'box'];

    public const TRANSMISSIONS = ['manual', 'automatic'];

    public const FUEL_TYPES = ['gasoline', 'diesel', 'electric', 'hybrid'];

    protected $fillable = [
        'brand',
        'model',
        'vehicle_type',
        'transmission',
        'fuel_type',
        'seat_capacity',
        'base_monthly_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'seat_capacity' => 'integer',
            'base_monthly_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected function brand(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? trim($value) : $value,
        );
    }

    protected function model(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? trim($value) : $value,
        );
    }
}
