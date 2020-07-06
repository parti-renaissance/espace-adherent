<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommandInterface;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ElectedRepresentativeChangeCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $repository;

    public function __construct(
        Manager $manager,
        ElectedRepresentativeRepository $repository,
        ObjectManager $entityManager
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->logger = new NullLogger();
    }

    public function __invoke(ElectedRepresentativeChangeCommandInterface $message): void
    {
        /** @var ElectedRepresentative $electedRepresentative */
        if (!$electedRepresentative = $this->repository->findOneByUuid($uuid = $message->getUuid()->toString())) {
            $this->logger->warning($error = sprintf('ElectedRepresentative with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($electedRepresentative);

        if ($adherent = $electedRepresentative->getAdherent()) {
            $this->entityManager->refresh($adherent);
        }

        $this->manager->editElectedRepresentativeMember($electedRepresentative, $message);

        $this->entityManager->clear();
    }
}
