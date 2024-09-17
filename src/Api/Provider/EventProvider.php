<?php

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\Event\BaseEventRepository;
use Ramsey\Uuid\Uuid;

class EventProvider implements ProviderInterface
{
    public function __construct(private readonly BaseEventRepository $eventRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = [])
    {
        $identifier = (string) $context['uri_variables']['uuid'] ?? null;

        if (empty($identifier)) {
            return null;
        }

        if (Uuid::isValid($identifier)) {
            return $this->eventRepository->findOneByUuid($identifier);
        }

        return $this->eventRepository->findOneBySlug($identifier);
    }
}
