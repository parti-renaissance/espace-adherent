<?php

namespace App\Controller\EnMarche\ApplicationRequestCandidate;

use App\ApplicationRequest\ApplicationRequestRepository;
use App\ApplicationRequest\ApplicationRequestTypeEnum;
use App\ApplicationRequest\Filter\ListFilter;
use App\Entity\ApplicationRequest\ApplicationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/", name="app_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentSpaceController extends AbstractApplicationRequestController
{
    private const SPACE_NAME = 'referent';

    protected function getApplicationRequests(
        ApplicationRequestRepository $repository,
        string $type,
        ListFilter $filter
    ): array {
        return $repository->findAllForReferentTags(
            $this->getUser()->getManagedArea()->getTags()->toArray(),
            $type,
            $filter
        );
    }

    protected function getSpaceName(): string
    {
        return self::SPACE_NAME;
    }

    protected function checkAccess(Request $request, ApplicationRequest $applicationRequest = null): void
    {
        if (
            ApplicationRequestTypeEnum::VOLUNTEER !== $request->attributes->get('type')
            && array_filter(
                $this->getUser()->getManagedAreaTagCodes(),
                function ($code) { return 0 === strpos($code, '75'); }
            )
        ) {
            throw $this->createNotFoundException();
        }
    }
}
