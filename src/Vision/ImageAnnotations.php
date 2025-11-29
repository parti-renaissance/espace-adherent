<?php

declare(strict_types=1);

namespace App\Vision;

use Symfony\Component\Serializer\Attribute\Groups;

class ImageAnnotations
{
    public const IDENTITY_DOCUMENT_LABEL = 'Identity document';
    public const NATIONAL_IDENTITY_CARD_LABEL = 'National identity card';
    public const PASSPORT_ENTITY_LABEL = 'Passport';
    public const FRENCH_PASSPORT_ENTITY_LABEL = 'French passport';

    public const PASSPORT_LABELS = [
        'passeport francais',
        'passeport biometrique',
        'passport francais',
        'french passport',
        'passport',
        'passeport',
    ];

    public const IDENTITY_CARD_LABELS = [
        'carte d identité française',
        'french id card',
        'carte d identite francaise',
        'carte nationale d identité',
        'cni francaise',
        'carte nationale d identite',
    ];

    #[Groups(['ocr'])]
    private $labels;

    #[Groups(['ocr'])]
    private $webEntities;

    #[Groups(['ocr'])]
    private $text;

    public function __construct(array $labels, array $webEntities, ?string $text)
    {
        $this->labels = $labels;
        $this->webEntities = $webEntities;
        $this->text = $text;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    public function getWebEntities(): array
    {
        return $this->webEntities;
    }

    public function setWebEntities(array $webEntities): void
    {
        $this->webEntities = $webEntities;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function isIdentityDocument(): bool
    {
        return 0 < \count(array_intersect($this->webEntities, [
            self::IDENTITY_DOCUMENT_LABEL,
            self::NATIONAL_IDENTITY_CARD_LABEL,
            self::PASSPORT_ENTITY_LABEL,
            self::FRENCH_PASSPORT_ENTITY_LABEL,
        ]));
    }

    public function isFrenchNationalIdentityCard(): bool
    {
        return $this->isIdentityDocument()
            && 0 < \count(array_intersect($this->labels, self::IDENTITY_CARD_LABELS));
    }

    public function isFrenchPassport(): bool
    {
        return $this->isIdentityDocument()
            && 0 < \count(array_intersect($this->labels, self::PASSPORT_LABELS));
    }

    public function isSupportedIdentityDocument(): bool
    {
        return $this->isIdentityDocument()
            && ($this->isFrenchNationalIdentityCard() || $this->isFrenchPassport());
    }
}
