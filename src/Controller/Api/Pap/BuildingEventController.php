<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingEvent;
use App\Pap\Command\BuildingEventCommand;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_ADHERENT')")
 */
class BuildingEventController extends AbstractController
{
    /**
     * @Route("/v3/pap/buildings/{uuid}/events",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     name="api_create_building_event",
     *     methods={"POST"}
     * )
     */
    public function __invoke(
        Request $request,
        Building $building,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
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

        $bus->dispatch(new BuildingEventCommand($building->getUuid(), $buildingEvent->getCampaign()->getUuid()));

        return $this->json('OK');
    }
}
