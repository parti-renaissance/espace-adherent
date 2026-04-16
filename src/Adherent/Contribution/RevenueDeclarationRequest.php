<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use Symfony\Component\Validator\Constraints as Assert;

class RevenueDeclarationRequest
{
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\NotBlank]
    public ?int $revenueAmount = null;
}
