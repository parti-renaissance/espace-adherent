<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\JeMengage\Alert\AlertTypeEnum;
use App\Repository\Poll\PollRepository;
use App\Repository\Poll\VoteRepository;

readonly class PollAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private PollRepository $pollRepository,
        private VoteRepository $voteRepository,
    ) {
    }

    public function getAlerts(?Adherent $adherent): array
    {
        if (!$poll = $this->pollRepository->findActivePollForAlert()) {
            return [];
        }

        $participated = $adherent ? $this->voteRepository->hasVoted($poll, $adherent) : null;

        $alert = new Alert(
            AlertTypeEnum::POLL,
            'Sondage',
            $poll->getQuestion(),
            (string) $poll->getDescription(),
            true === $participated ? 'Voir' : 'Je donne mon avis',
            '/sondage/'.$poll->getUuid()->toRfc4122(),
            data: [
                'uuid' => $poll->getUuid()->toRfc4122(),
                'question' => $poll->getQuestion(),
                'start_at' => $poll->getStartAt()->format(\DateTimeInterface::ATOM),
                'finish_at' => $poll->getFinishAt()->format(\DateTimeInterface::ATOM),
                'participated' => $participated,
            ],
        );
        $alert->date = $poll->getFinishAt();

        return [$alert];
    }
}
