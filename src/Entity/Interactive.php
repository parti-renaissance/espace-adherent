<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="interactive", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="interactive_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="interactive_slug_key_unique", columns="slug")
 * })
 */
class Interactive
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $meta;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $subtitle;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InteractiveChoice", mappedBy="interactive", cascade={"all"})
     */
    private $choices;

    public function __construct(Uuid $uuid = null)
    {
        $this->choices = new ArrayCollection();
        $this->uuid = $uuid ? $uuid :Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getMeta(): ?string
    {
        return $this->meta;
    }

    /**
     * @param string $meta
     */
    public function setMeta(string $meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     */
    public function setPicture(string $picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }

    public function getChoices(): iterable
    {
        return $this->choices;
    }

    public function setChoices(iterable $choices)
    {
        $this->choices->clear();

        foreach($choices as $choice) {
            $this->addChoice($choice);
        }
    }

    public function addChoice(InteractiveChoice $choice)
    {
        if ($this->choices->contains($choice)) {
            return ;
        }

        $choice->setInteractive($this);
        $this->choices->add($choice);
    }

    public function setUUID(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
