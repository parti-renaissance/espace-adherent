<?php

namespace App\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translation;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTranslationInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Entity\Timeline\MeasureTranslationListener"})
 * @ORM\Table(name="timeline_measure_translations")
 *
 * @UniqueEntity(fields={"locale", "title"}, errorPath="title")
 *
 * @Algolia\Index(autoIndex=false)
 */
class MeasureTranslation implements EntityTranslationInterface
{
    use Translation;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=Measure::TITLE_MAX_LENGTH)
     */
    private $title;

    public function __construct(string $locale = null, string $title = null)
    {
        $this->locale = $locale;
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function isEmpty(): bool
    {
        return empty($this->title);
    }
}
