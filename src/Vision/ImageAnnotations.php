<?php

namespace App\Vision;

use Symfony\Component\Serializer\Annotation\Groups;

class ImageAnnotations
{
    private const IDENTITY_DOCUMENT_LABEL = 'Identity document';
    private const NATIONAL_IDENTITY_CARD_LABEL = 'National identity card';
    private const PASSPORT_LABEL = 'Passport';
    private const FRENCH_PASSPORT_ENTITY_LABEL = 'French passport';

    private const FRENCH_IDENTITY_CARD_LABEL = 'carte d identité française';
    private const FRENCH_PASSPORT_LABEL = 'french passport';

    /**
     * @Groups({"ocr"})
     */
    private $labels;

    /**
     * @Groups({"ocr"})
     */
    private $webEntities;

    /**
     * @Groups({"ocr"})
     */
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
        return 0 < array_intersect($this->labels, [
            self::IDENTITY_DOCUMENT_LABEL,
            self::NATIONAL_IDENTITY_CARD_LABEL,
            self::PASSPORT_LABEL,
            self::FRENCH_PASSPORT_ENTITY_LABEL,
        ]);
    }

    public function isFrenchNationalIdentityCard(): bool
    {
        return $this->isIdentityDocument()
            && \in_array(self::FRENCH_IDENTITY_CARD_LABEL, $this->labels, true)
        ;
    }

    public function isFrenchPassport(): bool
    {
        return $this->isIdentityDocument()
            && \in_array(self::FRENCH_PASSPORT_LABEL, $this->labels, true)
        ;
    }

    public function isSupportedIdentityDocument(): bool
    {
        return $this->isIdentityDocument()
            && ($this->isFrenchNationalIdentityCard() || $this->isFrenchPassport())
        ;
    }
}
