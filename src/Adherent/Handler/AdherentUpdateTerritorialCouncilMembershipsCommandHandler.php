<?php

namespace App\Adherent\Handler;

use App\Repository\AdherentRepository;
use App\TerritorialCouncil\Command\AdherentUpdateTerritorialCouncilMembershipsCommand;
use App\TerritorialCouncil\Handlers\AbstractTerritorialCouncilHandler;
use App\TerritorialCouncil\Handlers\TerritorialCouncilMembershipHandlerInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentUpdateTerritorialCouncilMembershipsCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var TerritorialCouncilMembershipHandlerInterface[]|iterable
     */
    private $handlers;
    private $adherentRepository;
    private $entityManager;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        iterable $handlers,
        LoggerInterface $logger
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        $handlers = iterator_to_array($handlers);
        usort($handlers, function (AbstractTerritorialCouncilHandler $handlerA, AbstractTerritorialCouncilHandler $handlerB) {
            return $handlerA->getPriority() <=> $handlerB->getPriority();
        });
        $this->handlers = $handlers;
    }

    public function __invoke(AdherentUpdateTerritorialCouncilMembershipsCommand $command): void
    {
        $adherent = $this->adherentRepository->findByUuid($command->getUuid()->toString());

        if (!$adherent) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $firstException = null;

        foreach ($this->handlers as $handler) {
            if ($handler->supports($adherent)) {
                try {
                    $handler->handle($adherent);
                } catch (DBALException $e) {
                    $this->logger->error($e->getMessage(), ['e' => $e]);

                    if (null === $firstException) {
                        $firstException = $e;
                    }
                }
            }
        }

        if ($firstException) {
            throw $firstException;
        }

        $this->entityManager->flush();

        $this->entityManager->clear();
    }
}
