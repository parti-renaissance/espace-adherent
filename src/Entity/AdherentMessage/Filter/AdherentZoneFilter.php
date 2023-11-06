<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AdherentZoneFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;

    /**
     * @var ReferentTag
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ReferentTag")
     *
     * @Assert\NotBlank
     */
    private $referentTag;

    public function __construct(ReferentTag $referentTag = null)
    {
        parent::__construct();

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
