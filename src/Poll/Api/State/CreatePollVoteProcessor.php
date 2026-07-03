<?php

declare(strict_types=1);

namespace App\Poll\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use App\Repository\Poll\ChoiceRepository;
use App\Repository\Poll\PollRepository;
use App\Repository\Poll\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CreatePollVoteProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private PollRepository $pollRepository,
        private ChoiceRepository $choiceRepository,
        private VoteRepository $voteRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $poll = $this->pollRepository->findOneByUuid($uriVariables['uuid'] ?? '');

        if (!$poll) {
            throw new NotFoundHttpException('Le sondage n\'existe pas.');
        }

        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            throw new AccessDeniedHttpException();
        }

        $choice = $this->choiceRepository->findOneByUuid($data->choice);

        if (!$choice || !$choice->getPoll()->equals($poll)) {
            throw new NotFoundHttpException("Le choix '{$data->choice}' est introuvable.");
        }

        if (!$poll->isVotePeriodActive()) {
            throw new ConflictHttpException('La période de vote pour ce sondage est terminée.');
        }

        if ($this->voteRepository->hasVoted($poll, $user)) {
            throw new ConflictHttpException('Vous avez déjà voté pour ce sondage.');
        }

        if (!$choice instanceof Choice) {
            throw new AccessDeniedHttpException();
        }

        $this->entityManager->persist(new Vote($poll, $choice, $user));
        $this->entityManager->flush();
    }
}
