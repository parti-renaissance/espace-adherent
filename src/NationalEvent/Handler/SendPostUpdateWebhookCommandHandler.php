<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\SendWebhookCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SendPostUpdateWebhookCommandHandler
{
    public function __construct(
        private readonly HttpClientInterface $nationalEventTicketClient,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly string $appEnvironment,
    ) {
    }

    public function __invoke(SendWebhookCommand $command): void
    {
        if ('production' !== $this->appEnvironment) {
            return;
        }

        if (!$command->isPostUpdate()) {
            return;
        }

        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (!\in_array($eventInscription->status, [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE, InscriptionStatusEnum::REFUSED])) {
            return;
        }

        $response = $this->nationalEventTicketClient->request('POST', '/api/tickets', [
            'json' => [
                'code' => $uuid = $eventInscription->getUuid()->toString(),
                'event' => '/api/events/13',
                'blacklist' => InscriptionStatusEnum::REFUSED === $eventInscription->status,
                'custom' => [
                    'prenom' => $eventInscription->firstName,
                    'nom' => $eventInscription->lastName,
                ],
            ],
        ]);

        if (201 !== $response->getStatusCode()) {
            $this->nationalEventTicketClient->request('PATCH', '/api/events/13/tickets/'.$uuid, [
                'json' => [
                    'blacklist' => InscriptionStatusEnum::REFUSED === $eventInscription->status,
                    'custom' => [
                        'prenom' => $eventInscription->firstName,
                        'nom' => $eventInscription->lastName,
                        'champ_supp' => (string) $eventInscription->ticketCustomDetail,
                    ],
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ]);
        }
    }
}
