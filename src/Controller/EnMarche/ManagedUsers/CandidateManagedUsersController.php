<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Form\ManagedUsers\CandidateManagedUsersFilterType;
use App\ManagedUsers\ManagedUsersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat", name="app_candidate_managed_users_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS'))")
 */
class CandidateManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::CANDIDATE;
    }

    protected function createFilterForm(ManagedUsersFilter $filter = null): FormInterface
    {
        return $this->createForm(CandidateManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'space_type' => $this->getSpaceType(),
        ]);
    }
}
