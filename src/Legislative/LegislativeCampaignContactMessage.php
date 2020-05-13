<?php

namespace App\Legislative;

use Symfony\Component\Validator\Constraints as Assert;

final class LegislativeCampaignContactMessage
{
    private const HOTLINE_FINANCIAL = 'financial';
    private const HOTLINE_STANDARD = 'standard';

    private const HOTLINE_CHOICES = [
        'Hotline Comptes de campagne' => self::HOTLINE_FINANCIAL,
        'Hotline Campagne électorale' => self::HOTLINE_STANDARD,
    ];

    private const CAMPAIGN_ROLES = [
        'Candidat' => 'Candidat',
        'Suppléant' => 'Suppléant',
        'Directeur de campagne' => 'Directeur de campagne',
        'Responsable numérique' => 'Responsable numérique',
        'Responsable communication' => 'Responsable communication',
        'Responsable mobilisation' => 'Responsable mobilisation',
        'Mandataire financier' => 'Mandataire financier',
        'Autre' => 'Autre',
    ];

    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $firstName;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $lastName;

    /**
     * @Assert\NotBlank
     */
    private $departmentNumber;

    /**
     * @Assert\NotBlank
     */
    private $electoralDistrictNumber;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getRoles", strict=true, message="legislative_campaign_contact_message.role.invalid")
     */
    private $role;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getRecipients", strict=true, message="legislative_campaign_contact_message.recipient.invalid")
     */
    private $recipient;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=80)
     */
    private $subject;

    /**
     * @Assert\NotBlank
     */
    private $message;

    public static function getRoleChoices(): array
    {
        return self::CAMPAIGN_ROLES;
    }

    public static function getRoles(): array
    {
        return array_values(self::getRoleChoices());
    }

    public static function getRecipientChoices(): array
    {
        return self::HOTLINE_CHOICES;
    }

    public static function getRecipients(): array
    {
        return array_values(self::getRecipientChoices());
    }

    public function __construct()
    {
        $this->recipient = self::HOTLINE_STANDARD;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getDepartmentNumber(): ?string
    {
        return $this->departmentNumber;
    }

    public function setDepartmentNumber(?string $departmentNumber): void
    {
        $this->departmentNumber = $departmentNumber;
    }

    public function getElectoralDistrictNumber(): ?string
    {
        return $this->electoralDistrictNumber;
    }

    public function setElectoralDistrictNumber(?string $electoralDistrictNumber): void
    {
        $this->electoralDistrictNumber = $electoralDistrictNumber;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function isAddressedToFinancialHotline(): bool
    {
        return self::HOTLINE_FINANCIAL === $this->recipient;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }
}
