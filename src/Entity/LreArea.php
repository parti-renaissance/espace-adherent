<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class LreArea
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ReferentTag")
     * @ORM\JoinColumn(nullable=false)
     */
    private $referentTag;

    public function __construct(ReferentTag $tag = null)
    {
        $this->referentTag = $tag;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(?ReferentTag $referentTag): void
    {
        $this->referentTag = $referentTag;
    }
}
