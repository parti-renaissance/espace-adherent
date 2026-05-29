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

    /**
     * PKCE code challenge (S256). Optional and intentionally NOT format-validated here:
     * a malformed value must not fail the activation itself — it only means no auto-login
     * code is minted. The OAuth authorize step rejects an invalid challenge downstream.
     */
    #[Groups(['signup:write'])]
    public ?string $codeChallenge = null;
}
