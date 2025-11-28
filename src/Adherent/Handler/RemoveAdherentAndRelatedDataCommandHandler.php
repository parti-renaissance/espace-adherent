<?php

declare(strict_types=1);

namespace App\Adherent\Handler;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Unregistration\Handlers\UnregistrationAdherentHandlerInterface;
use App\Repository\AdherentRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveAdherentAndRelatedDataCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var UnregistrationAdherentHandlerInterface[]|iterable
     */
    private $handlers;
    private $adherentRepository;
    private $entityManager;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        iterable $handlers,
        LoggerInterface $logger,
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->handlers = $handlers;
        $this->logger = $logger;
    }

    public function __invoke(RemoveAdherentAndRelatedDataCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $firstException = null;

        foreach ($this->handlers as $handler) {
            if ($handler->supports($adherent)) {
                try {
                    $handler->handle($adherent);
                } catch (Exception $e) {
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

        $this->entityManager->remove($adherent);
        $this->entityManager->flush();

        $this->entityManager->clear();
    }
}
