<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Repository\AdherentRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentChangeEmailCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $bus;
    private $adherentRepository;
    private $electedRepresentativeRepository;

    public function __construct(
        MessageBusInterface $bus,
        AdherentRepository $adherentRepository,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
        $this->bus = $bus;
        $this->adherentRepository = $adherentRepository;
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
        $this->logger = new NullLogger();
    }

    public function __invoke(AdherentChangeCommandInterface $message): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($uuid = $message->getUuid()->toString())) {
            $this->logger->warning(sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        if ($adherent->getEmailAddress() !== $message->getEmailAddress()) {
            /** @var ElectedRepresentative $electedRepresentative */
            if ($electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent])) {
                $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
                    $electedRepresentative->getUuid(),
                    $message->getEmailAddress()
                ));
            }
        }
    }
}
