<?php

declare(strict_types=1);

namespace App\Membership\Signup;

use App\Address\Address;
use libphonenumber\PhoneNumber;

class SignupCommand
{
    public function __construct(
        public string $email,
        public string $source,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?PhoneNumber $phone = null,
        public ?string $gender = null,
        public ?Address $address = null,
        public bool $emailOptIn = false,
        public bool $smsOptIn = false,
        public ?string $utmSource = null,
        public ?string $utmCampaign = null,
        public ?string $referrerCode = null,
    ) {
    }
}
