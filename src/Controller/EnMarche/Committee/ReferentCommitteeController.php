<?php

namespace App\Controller\EnMarche\Committee;

use App\AdherentSpace\AdherentSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_COMMITTEE'))")
 */
#[Route(path: '/espace-referent/comites', name: 'app_referent_', methods: ['GET'])]
class ReferentCommitteeController extends AbstractCommitteeController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }

    protected function getWithProvisionalSupervisors(): bool
    {
        return true;
    }
}
