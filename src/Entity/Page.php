<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageRepository")
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Page implements EntityMediaInterface, EntityContentInterface, EntitySoftDeletedInterface
{
    public const LAYOUT_DEFAULT = 'default';
    public const LAYOUT_MUNICIPALES = 'municipales';

    public const LAYOUTS = [
        self::LAYOUT_DEFAULT,
        self::LAYOUT_MUNICIPALES,
    ];

    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use EntityMediaTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(options={"default": "default"})
     *
     * @Assert\Choice(choices=Page::LAYOUTS)
     */
    private $layout = self::LAYOUT_DEFAULT;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }
}
