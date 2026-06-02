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

    /** PKCE code challenge (S256) */
    #[Groups(['signup:write'])]
    public ?string $codeChallenge = null;

    #[Groups(['signup:write'])]
    public ?string $clientId = null;

    #[Groups(['signup:write'])]
    public ?string $redirectUri = null;

    public function wantsAuthorizationCode(): bool
    {
        return '' !== (string) $this->codeChallenge;
    }

    public function hasCompleteAuthorizationCodeRequest(): bool
    {
        return $this->wantsAuthorizationCode()
            && '' !== (string) $this->clientId
            && '' !== (string) $this->redirectUri;
    }
}
