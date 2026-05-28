<?php

declare(strict_types=1);

namespace App\Membership\Signup\Request;

use App\Membership\Signup\SignupCode;
use App\Validator\StrictEmail;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class SignupActivateRequest
{
    #[Assert\NotBlank]
    #[Groups(['signup:write'])]
    #[StrictEmail(dnsCheck: false, disabledEmail: false)]
    public ?string $email = null {
        set(?string $value) => null === $value ? null : mb_strtolower($value);
    }

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: SignupCode::PATTERN)]
    #[Groups(['signup:write'])]
    public ?string $code = null;
}
