<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'color',
        'name',
        'logo',
    ];

    /**
     *
     *
     * @return self
     */
    public static function getCurrentSettings(): self
    {

        return self::firstOrCreate([], [
            'color' => '#0095FF',
            'name' => 'Notes For Therapy',
            'logo' => null,
        ]);
    }
}