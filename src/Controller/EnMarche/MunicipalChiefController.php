<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Referent\MunicipalExporter;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(
 *     path="/espace-chef-municipal",
 *     name="app_municipal_chief_municipal_"
 * )
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers",
     *     name="running_mate_request",
     *     methods={"GET"},
     * )
     */
    public function municipalRunningMateRequestAction(
        RunningMateRequestRepository $runningMateRequestRepository,
        MunicipalExporter $municipalExporter,
        UserInterface $municipalChief
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/running_mate/list.html.twig', [
            'runningMateListJson' => $municipalExporter->exportRunningMateAsJson(
                $runningMateRequestRepository->findForMunicipalChief($municipalChief),
                'app_municipal_chief_municipal_running_mate_request_detail'
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole",
     *     name="volunteer_request",
     *     methods={"GET"},
     * )
     */
    public function municipalVolunteerAction(
        VolunteerRequestRepository $volunteerRequestRepository,
        MunicipalExporter $municipalExporter,
        UserInterface $municipalChief
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/volunteer/list.html.twig', [
            'volunteerListJson' => $municipalExporter->exportVolunteerAsJson(
                $volunteerRequestRepository->findForMunicipalChief($municipalChief),
                'app_municipal_chief_municipal_volunteer_request_detail'
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/detail",
     *     name="running_mate_request_detail",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     */
    public function municipalRunningMateDetailAction(
        UserInterface $municipalChief,
        RunningMateRequest $runningMateRequest
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/running_mate/detail.html.twig', [
            'runningMateRequest' => $runningMateRequest,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/detail",
     *     name="volunteer_request_detail",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     */
    public function municipalVolunteerDetailAction(
        UserInterface $municipalChief,
        VolunteerRequest $volunteerRequest
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/volunteer/detail.html.twig', [
            'volunteerRequest' => $volunteerRequest,
        ]);
    }
}
