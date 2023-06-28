<?php

namespace App\Donation\Request;

interface DonationRequestInterface
{
    public function getAmount(): ?float;

    public function getEmailAddress(): ?string;
}
