<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use App\ManagedUsers\ManagedUsersFilter;
use App\Subscription\SubscriptionTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur-delegue", name="app_senator_managed_users_delegated_", methods={"GET"})
 *
 * @Security("is_granted('IS_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS', 'senator')")
 */
class DelegatedSenatorManagedUsersController extends SenatorManagedUsersController
{
    protected function createFilterModel(): ManagedUsersFilter
    {
        /** @var DelegatedAccess $delegatedAccess */
        $delegatedAccess = $this->get('request_stack')->getMasterRequest()->attributes->get('delegated_access');
        if (!$delegatedAccess) {
            throw new \LogicException('No delegated access found');
        }

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::SENATOR_EMAIL,
            [$delegatedAccess->getDelegator()->getSenatorArea()->getDepartmentTag()],
            $delegatedAccess->getRestrictedCommittees()->map(static function (Committee $committee) {
                return $committee->getUuidAsString();
            })->toArray(),
            $delegatedAccess->getRestrictedCities(),
        );
    }
}
