<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\ApplicationRequest\ApplicationRequestTypeEnum;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Security\Voter\MunicipalChiefVoter;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(path="/espace-chef-municipal", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends Controller
{
    /**
     * @Route("candidature-colistiers/{uuid}/ajouter-a-mon-equipe", name="_running_mate_add_to_my_team", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     * @Route("candidature-benevoles/{uuid}/ajouter-a-mon-equipe", name="_volunteer_add_to_my_team", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     */
    public function addToMyTeamAction(
        ObjectManager $manager,
        ApplicationRequestRepository $repository,
        string $uuid,
        string $type
    ): Response {
        if (!$request = $repository->findOneByUuid($uuid, $type)) {
            $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(MunicipalChiefVoter::ROLE, $request);

        if ($request->getTakenForCity()) {
            throw new BadRequestHttpException('This request is already on another team.');
        }

        $request->setTakenForCity(current(array_intersect($request->getFavoriteCities(), $this->getUser()->getMunicipalChiefManagedArea->getCodes())));
        $manager->flush();

        $this->addFlash('info', 'application_request.taken_successfully');

        return $this->redirectToRoute("app_municipal_chief_candidate_${type}_list");
    }

    /**
     * @Route("candidature-colistiers/{uuid}/retirer-de-mon-equipe", name="_running_mate_remove_from_my_team", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     * @Route("candidature-benevoles/{uuid}/retirer-de-mon-equipe", name="_volunteer_remove_from_my_team", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     */
    public function removeFromMyTeamAction(
        Request $httpRequest,
        ObjectManager $manager,
        ApplicationRequestRepository $repository,
        string $uuid,
        string $type
    ): Response {
        if (!$request = $repository->findOneByUuid($uuid, $type)) {
            $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(MunicipalChiefVoter::ROLE, $request);

        if (!$request->getTakenForCity()) {
            throw new BadRequestHttpException('This request is not in your team.');
        }

        $request->setTakenForCity(null);
        $manager->flush();

        $this->addFlash('info', 'application_request.removed_successfully');

        $myTeamTarget = $httpRequest->query->has('mtt');

        return $this->redirectToRoute('app_municipal_chief_'.($myTeamTarget ? 'my_team' : 'candidate')."_${type}_list");
    }

    /**
     * @Route(path="/adherents", name="_adherents_list", methods={"GET"})
     */
    public function adherentsListAction(
        Request $request,
        UserInterface $municipalChief,
        AdherentRepository $adherentRepository
    ): Response {
        return $this->render('municipal_chief/adherent/list.html.twig', [
            'results' => $adherentRepository->findPaginatedForInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes(),
                $request->query->getInt('page')
            ),
        ]);
    }
}
