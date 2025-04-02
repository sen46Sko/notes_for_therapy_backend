<?php

namespace App\Enums;

class SystemActionType
{
    const USER_REGISTERED = 'user_registered';
    const USER_REGISTERED_VIA_APPLE = 'user_registered_via_apple';
    const USER_REGISTERED_VIA_GOOGLE = 'user_registered_via_google';
    const USER_LOGGED_IN = 'user_logged_in';
    const USER_LOGGED_IN_VIA_APPLE = 'user_logged_in_via_apple';
    const USER_LOGGED_IN_VIA_GOOGLE = 'user_logged_in_via_google';
    const USER_ACCOUNT_DELETED = 'user_account_deleted';
    const REPORT_CREATED = 'report_created';
    const TRIAL_STARTED = 'trial_started';
    const SUBSCRIPTION_MONTHLY = 'subscription_monthly';
    const SUBSCRIPTION_YEARLY = 'subscription_yearly';
    const SUBSCRIPTION_CANCELLED = 'subscription_cancelled';
    const TICKET_RESOLVED = 'ticket_resolved';
    const TICKET_CREATED = 'ticket_created';
    const GOALS_INTERACTION = 'goals_interaction';
    const MOODS_INTERACTION = 'moods_interaction';
    const HOMEWORKS_INTERACTION = 'homeworks_interaction';
    const SYMPTOMPS_INTERACTION = 'symptomps_interaction';
    const NOTES_INTERACTION = 'notes_interaction';
    private $value;

    /**
     * Create a new instance
     * @param string $value
     */
    public function __construct(string $value)
    {
        if (!self::isValid($value)) {
            throw new \InvalidArgumentException("Invalid action type: $value");
        }
        $this->value = $value;
    }

    /**
     * Get the value
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * String representation
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check if value is valid
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::getAll());
    }

    /**
     * Get all available action types
     * @return array
     */
    public static function getAll(): array
    {
        return [
            self::USER_REGISTERED,
            self::USER_REGISTERED_VIA_APPLE,
            self::USER_REGISTERED_VIA_GOOGLE,
            self::USER_LOGGED_IN,
            self::USER_LOGGED_IN_VIA_APPLE,
            self::USER_LOGGED_IN_VIA_GOOGLE,
            self::USER_ACCOUNT_DELETED,
            self::REPORT_CREATED,
        ];
    }

    /**
     * Create from value
     * @param string $value
     * @return self
     */
    public static function fromValue(string $value): self
    {
        return new self($value);
    }
}
