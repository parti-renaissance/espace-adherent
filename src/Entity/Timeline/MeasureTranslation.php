<?php

namespace App\Entity\Timeline;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"locale", "title"}, errorPath="title")
 */
#[ORM\Table(name: 'timeline_measure_translations')]
#[ORM\Entity]
#[ORM\EntityListeners([MeasureTranslationListener::class])]
class MeasureTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    protected $id;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=Measure::TITLE_MAX_LENGTH)
     */
    #[ORM\Column(length: 100)]
    private $title;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
