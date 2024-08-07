<?php

namespace App\Entity\Timeline;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'timeline_manifesto_translations')]
#[UniqueEntity(fields: ['locale', 'title'], errorPath: 'title')]
#[UniqueEntity(fields: ['locale', 'slug'], errorPath: 'slug')]
class ManifestoTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $title;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $slug;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private $description;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
