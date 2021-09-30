<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Form\ManagedUsers\ReferentManagedUsersFilterType;
use App\ManagedUsers\ManagedUsersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_managed_users_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS'))")
 */
class ReferentManagedUsersController extends AbstractManagedUsersController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }

    protected function createFilterForm(ManagedUsersFilter $filter, Adherent $mainAdherent): FormInterface
    {
        $currentAdherent = $this->getUser();

        return $this->createForm(ReferentManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'space_type' => $this->getSpaceType(),
            'fde_referent' => $mainAdherent->getManagedArea()->hasForeignTag(),
            'for_referent' => $currentAdherent && $currentAdherent->isReferent(),
        ]);
    }
}
