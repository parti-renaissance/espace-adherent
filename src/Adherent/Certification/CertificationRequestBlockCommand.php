<?php

namespace App\Adherent\Certification;

use Symfony\Component\Validator\Constraints as Assert;

class CertificationRequestBlockCommand extends AbstractCertificationRequestModerationCommand
{
    public const BLOCK_REASON_IDENTITY_THEFT = 'identity_theft';
    public const BLOCK_REASON_FALSE_DOCUMENT = 'false_document';
    public const BLOCK_REASON_MULTI_ACCOUNT = 'multi_account';
    public const BLOCK_REASON_OTHER = 'other';

    public const BLOCK_REASONS = [
        self::BLOCK_REASON_IDENTITY_THEFT,
        self::BLOCK_REASON_FALSE_DOCUMENT,
        self::BLOCK_REASON_MULTI_ACCOUNT,
        self::BLOCK_REASON_OTHER,
    ];

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=CertificationRequestBlockCommand::BLOCK_REASONS)
     */
    private $reason;

    /**
     * @var string|null
     *
     * @Assert\Length(max=500)
     * @Assert\Expression(
     *     expression="constant('App\\Adherent\\Certification\\CertificationRequestBlockCommand::BLOCK_REASON_OTHER') !== this.getReason() or value",
     *     message="Veuillez spécifier une raison de blocage."
     * )
     */
    private $customReason;

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getCustomReason(): ?string
    {
        return $this->customReason;
    }

    public function setCustomReason(?string $customReason): void
    {
        $this->customReason = $customReason;
    }
}
