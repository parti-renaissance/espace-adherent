<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Entity\Adherent;
use App\Form\ManagedUsers\ManagedUsersFilterType;
use App\ManagedUsers\ManagedUsersFilter;
use App\Subscription\SubscriptionTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur", name="app_senator_managed_users_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_SENATOR')")
 */
class SenatorManagedUsersController extends AbstractManagedUsersController
{
    private const SPACE_NAME = 'senator';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }

    protected function createFilterForm(ManagedUsersFilter $filter = null): FormInterface
    {
        return $this->createForm(ManagedUsersFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'single_zone' => true,
        ]);
    }

    protected function createFilterModel(Request $request): ManagedUsersFilter
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::SENATOR_EMAIL,
            [$adherent->getSenatorArea()->getDepartmentTag()]
        );
    }
}
