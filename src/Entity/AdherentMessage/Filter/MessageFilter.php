<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MessageFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;
}
