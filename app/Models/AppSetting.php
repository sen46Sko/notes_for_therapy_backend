<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'color',
        'name',
        'logo',
    ];

    /**
     * Get the current settings or create default
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

    /**
     * Get logo attribute with proper URL
     *
     * @param string $value
     * @return string|null
     */
    public function getLogoAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) || strpos($value, 'http') === 0) {
            return $value;
        }

        if (config('filesystems.default') === 's3') {
            return Storage::disk('s3')->url($value);
        }

        return asset('storage/' . $value);
    }

    /**
     * Set logo attribute
     *
     * @param string $value
     * @return void
     */
    public function setLogoAttribute($value)
    {
        if ($value && (strpos($value, 'http') === 0 || strpos($value, '//') === 0)) {
            $baseUrl = env('AWS_URL');
            if ($baseUrl && strpos($value, $baseUrl) === 0) {
                $this->attributes['logo'] = str_replace($baseUrl, '', $value);
                return;
            }

            $storageUrl = asset('storage/');
            if (strpos($value, $storageUrl) === 0) {
                $this->attributes['logo'] = str_replace($storageUrl . '/', '', $value);
                return;
            }

            $this->attributes['logo'] = $value;
            return;
        }


        $this->attributes['logo'] = $value;
    }
}