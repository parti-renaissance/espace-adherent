<?php

namespace App\Membership\MembershipRequest;

interface MembershipInterface
{
    public function getEmailAddress(): string;

    public function getSource(): ?string;
}
