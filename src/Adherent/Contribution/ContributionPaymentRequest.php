<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use App\Address\AddressInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionPaymentRequest
{
    #[Assert\Length(min: 2)]
    #[Assert\NotBlank]
    public ?string $accountName = null;

    #[Assert\Country(message: 'common.country.invalid')]
    #[Assert\NotBlank]
    public ?string $accountCountry = AddressInterface::FRANCE;

    #[Assert\Iban]
    #[Assert\NotBlank]
    public ?string $iban = null;
}
