<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Referent\MunicipalExporter;
use AppBundle\Repository\RunningMateRequestRepository;
use AppBundle\Repository\VolunteerRequestRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-chef-municipal")
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers",
     *     name="app_municipalchief_municipal_runningmaterequest",
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
            'managedCities' => FranceCitiesBundle::searchCitiesByInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes()
            ),
            'runningMateListJson' => $municipalExporter->exportRunningMateAsJson(
                $runningMateRequestRepository->findForMunicipalChief($municipalChief),
                'app_municipalchief_municipal_runningmaterequest_detail'
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole",
     *     name="app_municipalchief_municipal_volunteerrequest",
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
            'managedCities' => FranceCitiesBundle::searchCitiesByInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes()
            ),
            'volunteerListJson' => $municipalExporter->exportVolunteerAsJson(
                $volunteerRequestRepository->findForMunicipalChief($municipalChief),
                'app_municipalchief_municipal_volunteerrequest_detail'
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/detail",
     *     name="app_municipalchief_municipal_runningmaterequest_detail",
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
            'managedCities' => FranceCitiesBundle::searchCitiesByInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes()
            ),
            'runningMateRequest' => $runningMateRequest,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/detail",
     *     name="app_municipalchief_municipal_volunteerrequest_detail",
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
            'managedCities' => FranceCitiesBundle::searchCitiesByInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes()
            ),
            'volunteerRequest' => $volunteerRequest,
        ]);
    }
}
