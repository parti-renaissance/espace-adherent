<?php

namespace App\Controller\EnMarche\ApplicationRequestCandidate;

use App\AdherentSpace\AdherentSpaceEnum;
use App\ApplicationRequest\ApplicationRequestRepository;
use App\ApplicationRequest\ApplicationRequestTypeEnum;
use App\ApplicationRequest\Filter\ListFilter;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Form\ApplicationRequest\ApplicationRequestListFilterType;
use App\Security\Voter\MunicipalChiefVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020/", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefSpaceController extends AbstractApplicationRequestController
{
    /**
     * @Route("candidature-colistiers/mon-equipe", name="_my_team_running_mate_list", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     * @Route("candidature-benevoles/mon-equipe", name="_my_team_volunteer_list", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     */
    public function myTeamListAction(Request $request, ApplicationRequestRepository $repository, string $type): Response
    {
        $form = $this
            ->createForm(ApplicationRequestListFilterType::class, null, ['extended' => true])
            ->remove('isInMyTeam')
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $filter = $form->getData();
        } else {
            $filter = new ListFilter();
        }

        return $this->renderTemplate('application_request/space/my_team.html.twig', [
            'requests' => $repository->findAllTakenFor(
                $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode(),
                $type,
                $filter
            ),
            'request_type' => $type,
            'form' => $form->createView(),
        ]);
    }

    protected function getApplicationRequests(
        ApplicationRequestRepository $repository,
        string $type,
        ListFilter $filter
    ): array {
        $inseeCode = (array) $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode();
        $filter->setInseeCodes($inseeCode);

        return $repository->findAllForInseeCodes($inseeCode, $type, $filter);
    }

    protected function getSpaceName(): string
    {
        return AdherentSpaceEnum::MUNICIPAL_CHIEF;
    }

    protected function checkAccess(Request $request, ApplicationRequest $applicationRequest = null): void
    {
        if ($applicationRequest) {
            $this->denyAccessUnlessGranted(MunicipalChiefVoter::ROLE, $applicationRequest);
        }
    }

    protected function isExtendedFilterForm(): bool
    {
        return true;
    }
}
