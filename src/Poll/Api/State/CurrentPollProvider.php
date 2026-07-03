<?php

declare(strict_types=1);

namespace App\Poll\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;

readonly class CurrentPollProvider implements ProviderInterface
{
    public function __construct(private PollRepository $pollRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Poll
    {
        return $this->pollRepository->findLastActivePoll();
    }
}
