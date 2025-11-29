<?php

declare(strict_types=1);

namespace App\Donation\Request;

interface DonationRequestInterface
{
    public function getAmount(): ?float;

    public function getEmailAddress(): ?string;
}
