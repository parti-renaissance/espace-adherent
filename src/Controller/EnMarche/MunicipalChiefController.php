<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Entity\MunicipalChiefManagedArea;
use AppBundle\Form\ApplicationRequest\ApplicationRequestTagsType;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        UserInterface $municipalChief
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/running_mate/list.html.twig', [
            'running_mates' => $runningMateRequestRepository->findForMunicipalChief($municipalChief),
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
        UserInterface $municipalChief
    ): Response {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/volunteer/list.html.twig', [
            'volunteers' => $volunteerRequestRepository->findForMunicipalChief($municipalChief),
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
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', runningMateRequest)")
     */
    public function municipalRunningMateDetailAction(RunningMateRequest $runningMateRequest): Response
    {
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
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', volunteerRequest)")
     */
    public function municipalVolunteerDetailAction(VolunteerRequest $volunteerRequest): Response
    {
        $this->disableInProduction();

        return $this->render('municipal_chief/municipal/volunteer/detail.html.twig', [
            'volunteerRequest' => $volunteerRequest,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/ajouter-a-mon-equipe",
     *     name="volunteer_request_add_to_team",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', volunteerRequest)")
     */
    public function municipalVolunteerAddToTeamAction(
        ObjectManager $objectManager,
        UserInterface $municipalChief,
        VolunteerRequest $volunteerRequest
    ): Response {
        $this->disableInProduction();

        $this->addToTeam($objectManager, $volunteerRequest, $municipalChief->getMunicipalChiefManagedArea());

        return $this->redirectToRoute('app_municipal_chief_municipal_volunteer_request');
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/retirer-de-mon-equipe",
     *     name="volunteer_request_remove_from_team",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', volunteerRequest)")
     */
    public function municipalVolunteerRemoveFromTeamAction(
        ObjectManager $objectManager,
        UserInterface $municipalChief,
        VolunteerRequest $volunteerRequest
    ): Response {
        $this->disableInProduction();

        $this->removeFromTeam($objectManager, $volunteerRequest, $municipalChief->getMunicipalChiefManagedArea());

        return $this->redirectToRoute('app_municipal_chief_municipal_volunteer_request');
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/ajouter-a-mon-equipe",
     *     name="running_mate_request_add_to_team",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', runningMateRequest)")
     */
    public function municipalRunningMateAddToTeamAction(
        ObjectManager $objectManager,
        UserInterface $municipalChief,
        RunningMateRequest $runningMateRequest
    ): Response {
        $this->disableInProduction();

        $this->addToTeam($objectManager, $runningMateRequest, $municipalChief->getMunicipalChiefManagedArea());

        return $this->redirectToRoute('app_municipal_chief_municipal_running_mate_request');
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/retirer-de-mon-equipe",
     *     name="running_mate_request_remove_from_team",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', runningMateRequest)")
     */
    public function municipalRunningMateRemoveFromTeamAction(
        ObjectManager $objectManager,
        UserInterface $municipalChief,
        RunningMateRequest $runningMateRequest
    ): Response {
        $this->disableInProduction();

        $this->removeFromTeam($objectManager, $runningMateRequest, $municipalChief->getMunicipalChiefManagedArea());

        return $this->redirectToRoute('app_municipal_chief_municipal_running_mate_request');
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/editer-tags",
     *     name="running_mate_request_edit_tags",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', runningMateRequest)")
     */
    public function municipalRunningMateEditTagsAction(
        ObjectManager $objectManager,
        Request $request,
        RunningMateRequest $runningMateRequest
    ): Response {
        $this->disableInProduction();

        return $this->handleApplicationRequestTagsRequest(
            $objectManager,
            $request,
            $runningMateRequest,
            'municipal_chief/municipal/running_mate/edit_tags.html.twig',
            'app_municipal_chief_municipal_running_mate_request'
        );
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/editer-tags",
     *     name="volunteer_request_edit_tags",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"},
     * )
     *
     * @Security("is_granted('MUNICIPAL_CHIEF_OF', volunteerRequest)")
     */
    public function municipalVolunteerEditTagsAction(
        ObjectManager $objectManager,
        Request $request,
        VolunteerRequest $volunteerRequest
    ): Response {
        $this->disableInProduction();

        return $this->handleApplicationRequestTagsRequest(
            $objectManager,
            $request,
            $volunteerRequest,
            'municipal_chief/municipal/volunteer/edit_tags.html.twig',
            'app_municipal_chief_municipal_volunteer_request'
        );
    }

    private function addToTeam(
        ObjectManager $objectManager,
        ApplicationRequest $applicationRequest,
        MunicipalChiefManagedArea $municipalChiefManagedArea
    ): void {
        $cities = array_intersect(
            $municipalChiefManagedArea->getCodes(),
            $applicationRequest->getFavoriteCities()
        );
        if (!$applicationRequest->getTakenForCity() && !empty($city = reset($cities))) {
            $applicationRequest->setTakenForCity($city);
            $objectManager->flush();
        }
    }

    private function removeFromTeam(
        ObjectManager $objectManager,
        ApplicationRequest $applicationRequest,
        MunicipalChiefManagedArea $municipalChiefManagedArea
    ): void {
        if ($applicationRequest->getTakenForCity() && \in_array($applicationRequest->getTakenForCity(), $municipalChiefManagedArea->getCodes())) {
            $applicationRequest->setTakenForCity(null);
            $objectManager->flush();
        }
    }

    private function handleApplicationRequestTagsRequest(
        ObjectManager $objectManager,
        Request $request,
        ApplicationRequest $applicationRequest,
        string $view,
        string $redirectRoute
    ): Response {
        $form = $this
            ->createForm(ApplicationRequestTagsType::class, $applicationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $objectManager->flush();

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render($view, [
            'form' => $form->createView(),
        ]);
    }
}
