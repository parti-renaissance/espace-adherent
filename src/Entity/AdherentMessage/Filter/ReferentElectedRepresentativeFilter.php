<?php

namespace App\Entity\AdherentMessage\Filter;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentElectedRepresentativeFilter extends AbstractElectedRepresentativeFilter
{
    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ReferentTag")
     *
     * @Assert\NotNull
     */
    private $referentTag;

    public function __construct(ReferentTag $referentTag = null)
    {
        $this->referentTag = $referentTag;
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(ReferentTag $referentTag = null): void
    {
        $this->referentTag = $referentTag;
    }
}
