<?php

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MessageFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;
}
