<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\IdeasWorkshop\IdeaRepository;
use AppBundle\Repository\ThreadCommentRepository;
use AppBundle\Repository\ThreadRepository;
use AppBundle\Repository\VoteRepository;

class UpdateIdeaWorkshopHandler implements UnregistrationAdherentHandlerInterface
{
    private $ideaRepository;
    private $threadRepository;
    private $threadCommentRepository;
    private $voteRepository;

    public function __construct(
        IdeaRepository $ideaRepository,
        ThreadRepository $threadRepository,
        ThreadCommentRepository $threadCommentRepository,
        VoteRepository $voteRepository
    ) {
        $this->ideaRepository = $ideaRepository;
        $this->threadRepository = $threadRepository;
        $this->threadCommentRepository = $threadCommentRepository;
        $this->voteRepository = $voteRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->ideaRepository->removeNotFinalizedIdeas($adherent);
        $this->ideaRepository->anonymizeFinalizedIdeas($adherent);

        $this->threadRepository->removeAuthorItems($adherent);
        $this->threadCommentRepository->removeAuthorItems($adherent);

        $this->voteRepository->removeAuthorItems($adherent);
    }
}
