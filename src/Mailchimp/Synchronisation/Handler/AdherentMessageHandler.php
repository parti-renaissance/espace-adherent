<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Exception\AdherentNotFoundException;
use AppBundle\Mailchimp\Synchronisation\Manager;
use AppBundle\Mailchimp\Synchronisation\Message\AdherentMessageInterface;
use AppBundle\Repository\AdherentRepository;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentMessageHandler implements MessageHandlerInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $repository;

    public function __construct(Manager $manager, AdherentRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->logger = new NullLogger();
    }

    public function __invoke(AdherentMessageInterface $message): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->repository->findOneByUuid($uuid = $message->getUuid()->toString())) {
            $this->logger->warning($error = sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));
            throw new AdherentNotFoundException($error);
        }

        $this->repository->refresh($adherent);

        $this->manager->editMember($adherent, $message);
    }
}
