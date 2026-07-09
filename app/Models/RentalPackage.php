<?php

namespace App\Models;

use Database\Factories\RentalPackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalPackage extends Model
{
    /** @use HasFactory<RentalPackageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_months',
        'includes_driver',
        'includes_maintenance',
        'includes_insurance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
            'includes_driver' => 'boolean',
            'includes_maintenance' => 'boolean',
            'includes_insurance' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
