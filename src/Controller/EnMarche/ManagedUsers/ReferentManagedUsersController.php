<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Form\ManagedUsers\ReferentManagedUsersFilterType;
use App\ManagedUsers\ManagedUsersFilter;
use App\Subscription\SubscriptionTypeEnum;
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

    protected function createFilterModel(Request $request): ManagedUsersFilter
    {
        $session = $request->getSession();

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::REFERENT_EMAIL,
            $this->getMainUser($session)->getManagedArea()->getTags()->toArray(),
            $this->getRestrictedCommittees($session),
            $this->getRestrictedCities($session)
        );
    }
}
