<?php

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ReferentUserFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;
}
