<?php

namespace App\Adherent\Campus;

use App\Repository\AdherentRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentRegistrationCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly Client $client
    ) {
    }

    public function __invoke(AdherentRegistrationCommand $command): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent) {
            return;
        }

        $this->client->sendRegistrationRequest($adherent);
    }
}
