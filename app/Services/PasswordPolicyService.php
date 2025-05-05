<?php

namespace App\Services;

use App\Models\SecuritySettings;
use Illuminate\Support\Facades\Cache;

class PasswordPolicyService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = Cache::remember('security_settings', 300, function () {
            return SecuritySettings::first();
        });
    }

    public function validatePassword(string $password): array
    {
        $errors = [];

        if (!$this->settings) {
            return $errors;
        }

        if ($this->settings->password_length_enabled) {
            $length = strlen($password);
            if ($length < $this->settings->password_min_length) {
                $errors[] = "Password must be at least {$this->settings->password_min_length} characters long";
            }
            if ($length > $this->settings->password_max_length) {
                $errors[] = "Password must not exceed {$this->settings->password_max_length} characters";
            }
        }

        if ($this->settings->special_characters_enabled) {
            $specialChars = preg_match_all('/[^a-zA-Z0-9]/', $password);
            if ($specialChars < $this->settings->special_characters_min) {
                $errors[] = "Password must contain at least {$this->settings->special_characters_min} special characters";
            }
            if ($specialChars > $this->settings->special_characters_max) {
                $errors[] = "Password must not contain more than {$this->settings->special_characters_max} special characters";
            }
        }

        return $errors;
    }

    public function getPasswordRules(): array
    {
        if (!$this->settings) {
            return [
                'required',
                'string',
                'min:8'
            ];
        }

        $rules = ['required', 'string'];

        if ($this->settings->password_length_enabled) {
            $rules[] = "min:{$this->settings->password_min_length}";
            $rules[] = "max:{$this->settings->password_max_length}";
        }

        if ($this->settings->special_characters_enabled) {
            $rules[] = "regex:/^(?=(?:.*[^a-zA-Z0-9]){" . $this->settings->special_characters_min . "," . $this->settings->special_characters_max . "})/";
        }

        return $rules;
    }
}