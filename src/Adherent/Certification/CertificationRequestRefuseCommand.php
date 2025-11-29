<?php

declare(strict_types=1);

namespace App\Adherent\Certification;

use Symfony\Component\Validator\Constraints as Assert;

class CertificationRequestRefuseCommand extends AbstractCertificationRequestModerationCommand
{
    public const REFUSAL_REASON_DOCUMENT_NOT_IN_CONFORMITY = 'document_not_in_conformity';
    public const REFUSAL_REASON_DOCUMENT_NOT_READABLE = 'document_not_readable';
    public const REFUSAL_REASON_INFORMATIONS_NOT_MATCHING = 'informations_not_matching';
    public const REFUSAL_REASON_PROCESS_TIMEOUT = 'process_timeout';
    public const REFUSAL_REASON_BIRTH_DATE_NOT_MATCHING = 'birth_date_not_matching';
    public const REFUSAL_REASON_UNREADABLE_DOCUMENT = 'unreadable_document';
    public const REFUSAL_REASON_DOCUMENT_NOT_ORIGINAL = 'document_not_original';
    public const REFUSAL_REASON_REVERSED_FIRST_AND_LAST_NAME = 'reversed_first_and_last_name';
    public const REFUSAL_REASON_DOCUMENT_NOT_FULLY_VISIBLE = 'document_not_fully_visible';
    public const REFUSAL_REASON_DOCUMENT_NOT_FRONT = 'document_not_front';
    public const REFUSAL_REASON_PARTIAL_FIRST_NAME = 'partial_first_name';
    public const REFUSAL_REASON_OTHER = 'other';

    public const REFUSAL_REASONS = [
        self::REFUSAL_REASON_DOCUMENT_NOT_IN_CONFORMITY,
        self::REFUSAL_REASON_DOCUMENT_NOT_READABLE,
        self::REFUSAL_REASON_INFORMATIONS_NOT_MATCHING,
        self::REFUSAL_REASON_PROCESS_TIMEOUT,
        self::REFUSAL_REASON_BIRTH_DATE_NOT_MATCHING,
        self::REFUSAL_REASON_UNREADABLE_DOCUMENT,
        self::REFUSAL_REASON_DOCUMENT_NOT_ORIGINAL,
        self::REFUSAL_REASON_REVERSED_FIRST_AND_LAST_NAME,
        self::REFUSAL_REASON_DOCUMENT_NOT_FULLY_VISIBLE,
        self::REFUSAL_REASON_DOCUMENT_NOT_FRONT,
        self::REFUSAL_REASON_PARTIAL_FIRST_NAME,
        self::REFUSAL_REASON_OTHER,
    ];

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: CertificationRequestRefuseCommand::REFUSAL_REASONS)]
    #[Assert\NotBlank]
    private $reason;

    /**
     * @var string|null
     */
    #[Assert\Expression(expression: "constant('App\\\\Adherent\\\\Certification\\\\CertificationRequestRefuseCommand::REFUSAL_REASON_OTHER') !== this.getReason() or value", message: 'Veuillez spécifier une raison de refus.')]
    #[Assert\Length(max: 500)]
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
