<?php

namespace AppBundle\Controller\EnMarche\ManagedUsers;

use AppBundle\Entity\Adherent;
use AppBundle\Form\ManagedUsers\ReferentManagedUsersFilterType;
use AppBundle\ManagedUsers\ManagedUsersFilter;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_managed_users_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT') or is_granted('ROLE_COREFERENT')")
 */
class ReferentManagedUsersController extends AbstractManagedUsersController
{
    private const SPACE_NAME = 'referent';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function createFilterForm(ManagedUsersFilter $filter = null): FormInterface
    {
        return $this->createForm(ReferentManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }

    protected function createFilterModel(): ManagedUsersFilter
    {
        /** @var Adherent $referent */
        $referent = $this->getUser();

        $referentTeamMember = $referent->getReferentTeamMember();

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::REFERENT_EMAIL,
            ($referent->isCoReferent() ? $referent->getReferentOfReferentTeam() : $referent)
                ->getManagedArea()->getTags()->toArray(),
            $referent->isLimitedCoReferent() ? $referentTeamMember->getRestrictedCommitteeUuids() : [],
            $referent->isLimitedCoReferent() ? $referentTeamMember->getRestrictedCities() : []
        );
    }
}
