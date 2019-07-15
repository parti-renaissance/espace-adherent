<?php

namespace AppBundle\Controller\EnMarche\ApplicationRequestCandidate;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\ApplicationRequest\ApplicationRequestTypeEnum;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Security\Voter\MunicipalChiefVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-chef-municipal/", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefSpaceController extends AbstractApplicationRequestController
{
    private const SPACE_NAME = 'municipal_chief';

    /**
     * @Route("candidature-colistiers/mon-equipe", name="_my_team_running_mate_list", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     * @Route("candidature-benevoles/mon-equipe", name="_my_team_volunteer_list", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     */
    public function myTeamListAction(ApplicationRequestRepository $repository, string $type): Response
    {
        return $this->renderTemplate('application_request/space/my_team.html.twig', [
            'requests' => $repository->findAllTakenFor(
                $this->getUser()->getMunicipalChiefManagedArea()->getCodes(),
                $type
            ),
            'request_type' => $type,
        ]);
    }

    protected function getApplicationRequests(ApplicationRequestRepository $repository, string $type): array
    {
        return $repository->findAllForInseeCodes($this->getUser()->getMunicipalChiefManagedArea()->getCodes(), $type);
    }

    protected function getSpaceName(): string
    {
        return self::SPACE_NAME;
    }

    protected function checkAccess(ApplicationRequest $request = null): void
    {
        if ($request) {
            $this->denyAccessUnlessGranted(MunicipalChiefVoter::ROLE, $request);
        }
    }
}
