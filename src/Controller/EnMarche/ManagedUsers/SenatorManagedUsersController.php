<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Scope\ScopeEnum;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_SENATOR') or (is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS'))"))]
#[Route(path: '/espace-senateur', name: 'app_senator_managed_users_', methods: ['GET'])]
class SenatorManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::SENATOR;
    }

    protected function getScopeCode(): string
    {
        return ScopeEnum::SENATOR;
    }
}
