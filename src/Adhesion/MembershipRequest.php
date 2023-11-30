<?php

namespace App\Adhesion;

use App\Address\Address;

class MembershipRequest
{
    public ?string $email = null;
    public ?string $civility = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?Address $address = null;
    public ?int $amount = null;
    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
}
