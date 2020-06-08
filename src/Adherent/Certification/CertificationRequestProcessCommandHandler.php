<?php

namespace App\Adherent\Certification;

use App\Adherent\Certification\Handlers\CertificationRequestHandlerInterface;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CertificationRequestProcessCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $em;
    private $certificationRequestRepository;

    /**
     * @var CertificationRequestHandlerInterface[]|iterable
     */
    private $handlers;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository,
        iterable $handlers,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;
        $this->handlers = $handlers;
        $this->logger = $logger;
    }

    public function __invoke(CertificationRequestProcessCommand $command): void
    {
        /** @var CertificationRequest|null $certificationRequest */
        $certificationRequest = $this->certificationRequestRepository->findOneByUuid($command->getUuid());

        if (!$certificationRequest) {
            return;
        }

        $this->em->refresh($certificationRequest);

        $firstException = null;

        foreach ($this->handlers as $handler) {
            if ($handler->supports($certificationRequest)) {
                try {
                    $handler->handle($certificationRequest);
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

        $this->em->flush();
        $this->em->clear();
    }
}
