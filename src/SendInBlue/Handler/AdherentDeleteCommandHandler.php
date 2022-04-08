<?php

namespace App\SendInBlue\Handler;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use App\SendInBlue\AdherentManager;
use App\SendInBlue\Command\AdherentDeleteCommand;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentDeleteCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EntityManagerInterface $em;
    private AdherentRepository $adherentRepository;
    private AdherentManager $adherentManager;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        AdherentManager $adherentManager,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->adherentManager = $adherentManager;
        $this->logger = $logger;
    }

    public function __invoke(AdherentDeleteCommand $command): void
    {
        /** @var Adherent|null $adherent */
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent) {
            return;
        }

        $this->em->refresh($adherent);

        try {
            $this->adherentManager->delete($adherent);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to delete adherent UUID: "%s". Error: %s', $adherent->getUuid(), $e->getMessage()));
        }
    }
}
