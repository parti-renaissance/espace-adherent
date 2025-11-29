<?php

declare(strict_types=1);

namespace App\Committee\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InitializeCommitteeElectionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly CommitteeRepository $committeeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE]];
    }

    public function onPostWrite(ViewEvent $viewEvent): void
    {
        /** @var Designation $designation */
        $designation = $viewEvent->getControllerResult();

        if (
            !$designation instanceof Designation
            || !$designation->isCommitteeSupervisorType()
            || !$designation->getElectionEntityIdentifier()
        ) {
            return;
        }

        $request = $viewEvent->getRequest();
        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        foreach ($this->committeeRepository->findAllWithoutStartedElection($designation) as $committee) {
            $committee->setCurrentElection(new CommitteeElection($designation));
        }

        $this->entityManager->flush();
    }
}
