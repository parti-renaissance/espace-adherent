<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingEvent;
use App\Pap\Command\BuildingEventAsyncCommand;
use App\Pap\Command\BuildingEventCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
class BuildingEventController extends AbstractController
{
    #[Route(path: '/v3/pap/buildings/{uuid}/events', requirements: ['uuid' => '%pattern_uuid%'], name: 'api_create_building_event', methods: ['POST'])]
    public function __invoke(
        Request $request,
        Building $building,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
    ): JsonResponse {
        $buildingEvent = new BuildingEvent($building);
        $buildingEvent = $serializer->deserialize($request->getContent(), BuildingEvent::class, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $buildingEvent,
            AbstractNormalizer::GROUPS => ['pap_building_event_write'],
        ]);

        $errors = $validator->validate($buildingEvent);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($buildingEvent);
        $entityManager->flush();

        try {
            $bus->dispatch(new BuildingEventCommand($building->getUuid(), $buildingEvent->getCampaign()->getUuid()));
        } catch (\RuntimeException $exception) {
            $bus->dispatch(new BuildingEventAsyncCommand($building->getUuid(), $buildingEvent->getCampaign()->getUuid()));
        }

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
