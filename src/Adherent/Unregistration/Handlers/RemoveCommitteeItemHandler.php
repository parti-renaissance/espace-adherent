<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\CommitteeFeedItemRepository;

class RemoveCommitteeItemHandler implements UnregistrationAdherentHandlerInterface
{
    private $repository;

    public function __construct(CommitteeFeedItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->repository->removeAuthorItems($adherent);
    }
}
