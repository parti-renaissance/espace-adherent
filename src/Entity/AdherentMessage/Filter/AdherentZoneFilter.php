<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AdherentZoneFilter extends AbstractAdherentMessageFilter
{
    /**
     * @var ReferentTag
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     *
     * @Assert\NotBlank
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

    public function setReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTag = $referentTag;
    }
}
