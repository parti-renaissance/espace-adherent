<?php

namespace AppBundle\Entity\ManagedArea;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class ManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $since;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSince(): ?\DateTimeInterface
    {
        return $this->since;
    }

    public function setSince(\DateTimeInterface $since = null): void
    {
        $this->since = $since;
    }

    abstract public function isValid(): bool;
}
