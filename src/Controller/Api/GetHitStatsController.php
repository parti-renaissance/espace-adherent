<?php

namespace App\Controller\Api;

use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\Stats\Aggregator;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\AppHitRepository;
use App\Scope\FeatureEnum;
use App\Security\Voter\ManageZoneableItemVoter;
use App\Security\Voter\PublicationVoter;
use App\Security\Voter\ScopeFeatureVoter;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v3/stats/{type}/{uuid}', requirements: ['type' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: 'GET')]
class GetHitStatsController extends AbstractController
{
    private const TYPE_FEATURE_MAPPING = [
        TargetTypeEnum::Event->value => FeatureEnum::EVENTS,
        TargetTypeEnum::News->value => FeatureEnum::NEWS,
        TargetTypeEnum::Publication->value => FeatureEnum::PUBLICATIONS,
        TargetTypeEnum::Action->value => FeatureEnum::ACTIONS,
    ];

    private const TYPE_ENTITY_MAPPING = [
        TargetTypeEnum::Event->value => Event::class,
        TargetTypeEnum::News->value => News::class,
        TargetTypeEnum::Publication->value => AdherentMessage::class,
        TargetTypeEnum::Action->value => Action::class,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Aggregator $aggregator,
        private readonly AppHitRepository $hitRepository,
    ) {
    }

    #[Route]
    public function getOpenAndImpressionStats(string $type, string $uuid): Response
    {
        if ($response = $this->checkAccess($type, $uuid)) {
            return $response;
        }

        return $this->json($this->aggregator->getStats(TargetTypeEnum::from($type), Uuid::fromString($uuid), true));
    }

    #[Route('/{eventType}')]
    public function getImpressionStats(Request $request, string $type, string $uuid, string $eventType): Response
    {
        if (!$eventType = EventTypeEnum::tryFrom($eventType)) {
            throw $this->createNotFoundException('Invalid event type');
        }

        if ($response = $this->checkAccess($type, $uuid)) {
            return $response;
        }

        return $this->json($this->hitRepository->getPaginatedStats(
            $eventType,
            TargetTypeEnum::from($type),
            Uuid::fromString($uuid),
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 10)
        ), context: ['groups' => ['hit:read', ImageExposeNormalizer::NORMALIZATION_GROUP, 'hit:'.$eventType->value.':read']]);
    }

    private function checkAccess(string $type, string $uuid): ?Response
    {
        if (!($type = TargetTypeEnum::tryFrom($type)) || !\array_key_exists($type->value, self::TYPE_ENTITY_MAPPING)) {
            return $this->json(['error' => 'Invalid type'], Response::HTTP_BAD_REQUEST);
        }

        if (\array_key_exists($type->value, self::TYPE_FEATURE_MAPPING) && !$this->isGranted(ScopeFeatureVoter::SCOPE_AND_FEATURE_GRANTED, [self::TYPE_FEATURE_MAPPING[$type->value]])) {
            return $this->json(['error' => 'Access to this resource is forbidden'], Response::HTTP_FORBIDDEN);
        }

        $repository = $this->entityManager->getRepository(self::TYPE_ENTITY_MAPPING[$type->value]);

        if (!$object = $repository->findOneByUuid($uuid)) {
            return $this->json(['error' => 'Object not found'], Response::HTTP_NOT_FOUND);
        }

        if ($object instanceof AdherentMessageInterface) {
            if (!$this->isGranted(PublicationVoter::PERMISSION, $object)) {
                return $this->json(['error' => 'Access to this resource is forbidden'], Response::HTTP_FORBIDDEN);
            }
        } elseif (!$this->isGranted(ManageZoneableItemVoter::PERMISSION, $object)) {
            return $this->json(['error' => 'Access to this resource is forbidden'], Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
