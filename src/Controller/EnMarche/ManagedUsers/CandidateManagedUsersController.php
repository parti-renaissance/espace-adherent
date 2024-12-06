<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Form\ManagedUsers\CandidateManagedUsersFilterType;
use App\ManagedUsers\ManagedUsersFilter;
use App\Scope\ScopeEnum;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS'))"))]
#[Route(path: '/espace-candidat', name: 'app_candidate_managed_users_', methods: ['GET'])]
class CandidateManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::CANDIDATE;
    }

    protected function getScopeCode(): string
    {
        return ScopeEnum::CANDIDATE;
    }

    protected function createFilterForm(ManagedUsersFilter $filter, Adherent $mainAdherent): FormInterface
    {
        return $this->createForm(CandidateManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'space_type' => $this->getSpaceType(),
        ]);
    }
}
