<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Scope\ScopeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-depute', name: 'app_deputy_managed_users_', methods: ['GET'])]
#[Security("is_granted('ROLE_DEPUTY') or (is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS'))")]
class DeputyManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::DEPUTY;
    }

    protected function getScopeCode(): string
    {
        return ScopeEnum::DEPUTY;
    }
}
