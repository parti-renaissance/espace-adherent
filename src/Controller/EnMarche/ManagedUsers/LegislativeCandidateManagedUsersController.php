<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat-legislative", name="app_legislative_candidate_managed_users_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_LEGISLATIVE_CANDIDATE')")
 */
class LegislativeCandidateManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::LEGISLATIVE_CANDIDATE;
    }
}
