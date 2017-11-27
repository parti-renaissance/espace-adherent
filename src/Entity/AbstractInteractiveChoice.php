<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="interactive_choices", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="interactive_choices_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="interactive_choices_content_key_unique", columns="content_key")
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "purchasing_power" = "AppBundle\Entity\InteractiveChoice",
 * })
 * @Algolia\Index(autoIndex=false)
 */
abstract class AbstractInteractiveChoice
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\Column(type="smallint", length=1, options={"unsigned": true})
     */
    protected $step;

    /**
     * @ORM\Column(length=30)
     */
    protected $contentKey;

    /**
     * @ORM\Column(length=100)
     */
    protected $label;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Interactive", inversedBy="choices", fetch="LAZY")
     * @ORM\JoinColumn(name="interactive_id", referencedColumnName="id")
     */
    protected $interactive;

    public function __construct(UuidInterface $uuid = null, string $step = null, string $contentKey = null, string $label = null, string $content = null, Interactive $interactive = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->step = $step;
        $this->contentKey = $contentKey;
        $this->label = $label;
        $this->content = $content;
        $this->interactive = $interactive;
    }

    public function __toString(): string
    {
        return $this->label ?: '';
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getContentKey(): ?string
    {
        return $this->contentKey;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return mixed
     */
    public function getInteractive()
    {
        return $this->interactive;
    }

    /**
     * @param mixed $interactive
     */
    public function setInteractive(Interactive $interactive)
    {
        $this->interactive = $interactive;
    }
}
