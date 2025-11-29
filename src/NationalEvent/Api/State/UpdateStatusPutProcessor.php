<?php

declare(strict_types=1);

namespace App\NationalEvent\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\NationalEvent\DTO\RemainingStatsOutput;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UpdateStatusPutProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly EventInscriptionRepository $repository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RemainingStatsOutput
    {
        $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        return new RemainingStatsOutput(
            $this->repository->count(['status' => InscriptionStatusEnum::PENDING, 'event' => $data->event]),
            'Status successfully updated'
        );
    }
}
