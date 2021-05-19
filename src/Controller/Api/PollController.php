<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Vote;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Poll\ChoiceRepository;
use App\Repository\Poll\LocalPollRepository;
use App\Repository\Poll\NationalPollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PollController extends AbstractController
{
    /**
     * @Route(
     *     "/v3/polls/vote",
     *     name="api_polls_vote",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"POST"}
     * )
     */
    public function vote(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChoiceRepository $choiceRepository
    ): JsonResponse {
        $body = json_decode($request->getContent(), true);
        $uuid = $body['uuid'] ?? null;

        if (empty($uuid)) {
            throw new BadRequestHttpException('Parameter "uuid" is missing or empty.');
        }

        /* @var Choice|null $user */
        $choice = $choiceRepository->findOneByUuid($uuid);

        if (!$choice) {
            throw new NotFoundHttpException("Choice with uuid '$uuid' does not exist.");
        }

        /* @var Adherent|Device|null $user */
        $user = $this->getUser();

        if ($user instanceof Adherent) {
            $vote = Vote::createForAdherent($choice, $user);
        } elseif ($user instanceof Device) {
            $vote = Vote::createForDevice($choice, $user);
        } else {
            $vote = Vote::createForAnonymous($choice);
        }

        $entityManager->persist($vote);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($choice->getPoll(), 'json', ['groups' => ['poll_read']]),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }

    /**
     * @Route("/v3/polls", name="api_poll", methods={"GET"})
     * @Route("/v3/polls/{postalCode}", name="api_poll_by_postal_code", methods={"GET"})
     *
     * @throws NotFoundHttpException If no poll to return
     */
    public function getPollByPostalCode(
        ?string $postalCode,
        NationalPollRepository $nationalPollRepository,
        LocalPollRepository $localPollRepository,
        ZoneRepository $zoneRepository
    ): JsonResponse {
        $poll = $nationalPollRepository->findLastActivePoll();

        if (!$poll && $postalCode && ($region = $zoneRepository->findRegionByPostalCode($postalCode))) {
            $poll = $localPollRepository->findOnePublishedByZone($region);
        }

        if (!$poll) {
            $poll = $localPollRepository->findLastActivePoll();
        }

        if (!$poll) {
            throw $this->createNotFoundException();
        }

        return $this->json($poll, Response::HTTP_OK, [], ['groups' => ['poll_read']]);
    }
}
