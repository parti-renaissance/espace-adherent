<?php

declare(strict_types=1);

namespace App\VotingPlatform\Listener;

use App\Entity\VotingPlatform\ElectionResult\CandidateGroupResult;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AttributeAdditionalElectedPlacesListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VotingPlatformElectionVoteIsOverEvent::class => 'onVoteClose',
        ];
    }

    public function onVoteClose(VotingPlatformElectionVoteIsOverEvent $event): void
    {
        $election = $event->getElection();

        if (DesignationTypeEnum::COPOL !== $election->getDesignationType()) {
            return;
        }

        if (!$additionalPlaces = $election->getAdditionalPlaces()) {
            return;
        }

        $candidateGroupResults = $election->getElectionResult()->getCandidateGroupResults();

        // keep only not elected
        $candidateGroupResults = array_filter($candidateGroupResults, function (CandidateGroupResult $result) use ($election) {
            return
                $result->getTotal() > 0
                && !$result->getCandidateGroup()->isElected()
                && \in_array($election->getAdditionalPlacesGender(), $result->getCandidateGroup()->getGenders(), true);
        });

        // sort by total votes
        usort($candidateGroupResults, function (CandidateGroupResult $a, CandidateGroupResult $b) {
            return $b->getTotal() <=> $a->getTotal();
        });

        $count = \count($candidateGroupResults);

        $length = $additionalPlaces > $count ? $count : $additionalPlaces;

        for ($i = 1; $i <= $length; ++$i) {
            /** @var CandidateGroupResult $candidateGroupResult */
            $candidateGroupResult = array_shift($candidateGroupResults);

            if ($candidate = $candidateGroupResult->getCandidateGroup()->getCandidateByGender($election->getAdditionalPlacesGender())) {
                $candidate->setAdditionallyElected(true);
            }
        }

        $this->entityManager->flush();
    }
}
