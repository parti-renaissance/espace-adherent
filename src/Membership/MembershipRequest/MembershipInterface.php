<?php

declare(strict_types=1);

namespace App\Membership\MembershipRequest;

interface MembershipInterface
{
    public function getEmailAddress(): ?string;

    public function getSource(): ?string;
}
