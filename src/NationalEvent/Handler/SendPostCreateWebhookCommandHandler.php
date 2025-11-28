<?php

declare(strict_types=1);

namespace App\NationalEvent\Handler;

use App\NationalEvent\Command\SendWebhookCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SendPostCreateWebhookCommandHandler
{
    public function __construct(
        private readonly HttpClientInterface $nationalEventWebhookClient,
        private readonly NormalizerInterface $normalizer,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
    ) {
    }

    public function __invoke(SendWebhookCommand $command): void
    {
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->nationalEventWebhookClient->request('POST', '', [
            'json' => $this->normalizer->normalize($eventInscription, 'json', [
                AbstractNormalizer::GROUPS => ['national_event_inscription:webhook'],
                AbstractNormalizer::CALLBACKS => [
                    'birthdate' => function ($innerObject) {
                        return $innerObject instanceof \DateTime ? $innerObject->format('Y-m-d') : $innerObject;
                    },
                ],
            ]),
        ]);
    }
}
