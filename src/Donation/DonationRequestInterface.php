<?php

namespace App\Donation;

interface DonationRequestInterface
{
    public function getAmount(): ?float;

    public function getEmailAddress(): ?string;
}
