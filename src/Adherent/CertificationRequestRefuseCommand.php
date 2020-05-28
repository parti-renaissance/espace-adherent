<?php

namespace App\Adherent;

use Symfony\Component\Validator\Constraints as Assert;

class CertificationRequestRefuseCommand extends CertificationRequestModerationCommand
{
    public const REFUSAL_REASON_DOCUMENT_NOT_IN_CONFORMITY = 'document_not_in_conformity';
    public const REFUSAL_REASON_DOCUMENT_NOT_READABLE = 'document_not_readable';
    public const REFUSAL_REASON_INFORMATIONS_NOT_MATCHING = 'informations_not_matching';
    public const REFUSAL_REASON_OTHER = 'other';

    public const REFUSAL_REASONS = [
        self::REFUSAL_REASON_DOCUMENT_NOT_IN_CONFORMITY,
        self::REFUSAL_REASON_DOCUMENT_NOT_READABLE,
        self::REFUSAL_REASON_INFORMATIONS_NOT_MATCHING,
        self::REFUSAL_REASON_OTHER,
    ];

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=CertificationRequestRefuseCommand::REFUSAL_REASONS)
     */
    private $reason;

    /**
     * @var string|null
     *
     * @Assert\Length(max=500)
     * @Assert\Expression(
     *     expression="constant('App\\Adherent\\CertificationRequestRefuseCommand::REFUSAL_REASON_OTHER') !== this.getReason() or value",
     *     message="Veuillez spécifier une raison de refus."
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
