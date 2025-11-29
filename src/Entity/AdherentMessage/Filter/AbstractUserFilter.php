<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
abstract class AbstractUserFilter extends AbstractAdherentMessageFilter implements AdherentSegmentAwareFilterInterface, CampaignAdherentMessageFilterInterface
{
    use GeneralFilterTrait;
    use AdherentSegmentAwareFilterTrait;
}
