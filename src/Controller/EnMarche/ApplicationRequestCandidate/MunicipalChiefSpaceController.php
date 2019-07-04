<?php

namespace AppBundle\Controller\EnMarche\ApplicationRequestCandidate;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Security\Voter\MunicipalChiefVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-chef-municipal/", name="app_application_request_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefSpaceController extends AbstractApplicationRequestController
{
    private const SPACE_NAME = 'municipal_chief';

    protected function getApplicationRequests(ApplicationRequestRepository $repository, string $type): array
    {
        return $repository->findAllForInseeCodes($this->getUser()->getMunicipalChiefManagedArea()->getCodes(), $type);
    }

    protected function getSpaceName(): string
    {
        return self::SPACE_NAME;
    }

    protected function checkAccess(ApplicationRequest $request): void
    {
        $this->denyAccessUnlessGranted(MunicipalChiefVoter::ROLE, $request);
    }
}
