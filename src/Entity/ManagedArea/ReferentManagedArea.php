<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReferentManagedArea extends ManagedArea
{
    use ManagedTag;

    public function __construct(Adherent $adherent = null, ReferentTag $tag = null)
    {
        parent::__construct($adherent);

        $this->tag = $tag;
    }
}
