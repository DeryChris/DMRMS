<?php

namespace App\Services\Security;

use App\Models\SystemSetting;
use Illuminate\Validation\Rules\Password;

class PasswordPolicyService
{
    public function getValidationRules(): array
    {
        $minLength = SystemSetting::getValue('password_min_length', 8);
        $requireSpecial = SystemSetting::getValue('password_require_special', true, 'boolean');
        $requireNumbers = SystemSetting::getValue('password_require_numbers', true, 'boolean');
        $requireUppercase = SystemSetting::getValue('password_require_uppercase', true, 'boolean');

        $password = Password::min($minLength);

        if ($requireUppercase) {
            $password = $password->mixedCase();
        }

        if ($requireNumbers) {
            $password = $password->numbers();
        }

        if ($requireSpecial) {
            $password = $password->symbols();
        }

        return ['required', 'confirmed', $password];
    }

    public function getMinLength(): int
    {
        return SystemSetting::getValue('password_min_length', 8);
    }

    public function getExpiryDays(): int
    {
        return SystemSetting::getValue('password_expiry_days', 0);
    }

    public function isPasswordExpired(?\Carbon\Carbon $passwordChangedAt): bool
    {
        $expiryDays = $this->getExpiryDays();

        if ($expiryDays <= 0 || !$passwordChangedAt) {
            return false;
        }

        return $passwordChangedAt->addDays($expiryDays)->isPast();
    }
}
