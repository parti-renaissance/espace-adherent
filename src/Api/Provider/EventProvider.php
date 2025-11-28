<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\Event\EventRepository;
use Ramsey\Uuid\Uuid;

class EventProvider implements ProviderInterface
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $identifier = (string) ($context['uri_variables']['uuid'] ?? null);

        if (empty($identifier)) {
            return null;
        }

        if (Uuid::isValid($identifier)) {
            return $this->eventRepository->findOneByUuid($identifier);
        }

        return $this->eventRepository->findOneBySlug($identifier);
    }
}
