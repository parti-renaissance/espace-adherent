<?php

namespace App\Adherent\Certification;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CertificationRequestProcessCommandHandler implements MessageHandlerInterface
{
    public function __invoke(CertificationRequestProcessCommand $command): void
    {
        dump($command->getUuid());
    }
}
