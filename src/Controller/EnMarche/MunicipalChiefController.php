<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\StatisticsAggregator;
use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\ApplicationRequest\ApplicationRequestTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentMessageRepository;
use AppBundle\Repository\MunicipalEventRepository;
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
 * @Route(path="/espace-municipales-2020", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends Controller
{
    /**
     * @Route(name="_home", methods={"GET"})
     */
    public function homeAction(
        MunicipalEventRepository $eventRepository,
        ApplicationRequestRepository $candidateRepository,
        AdherentMessageRepository $messageRepository,
        StatisticsAggregator $aggregator,
        UserInterface $adherent
    ): Response {
        /** @var Adherent $adherent */
        $codes = (array) $adherent->getMunicipalChiefManagedArea()->getInseeCode();

        return $this->render('municipal_chief/home.html.twig', [
            'candidate_count' => $candidateRepository->countCandidates($codes),
            'team_member_count' => $candidateRepository->countTeamMembers($codes),
            'event_count' => $eventRepository->countEventForOrganizer($adherent),
            'last_sent_message' => $message = $messageRepository->getLastSentMessage($adherent, AdherentMessageTypeEnum::MUNICIPAL_CHIEF),
            'message_stats' => $message ? $aggregator->aggregateData($message) : null,
        ]);
    }

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

        $request->setTakenForCity(current(array_intersect(
            $request->getFavoriteCities(),
            (array) $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode())
        ));
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
}
