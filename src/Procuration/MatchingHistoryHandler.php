<?php

declare(strict_types=1);

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Procuration\MatchingHistory;
use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\Request;
use App\Entity\Procuration\Round;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MatchingHistoryHandler
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createMatch(Request $request, Proxy $proxy, Round $round, bool $emailCopy): MatchingHistory
    {
        $history = MatchingHistory::createMatch($request, $proxy, $round, $emailCopy);

        $this->addMatcher($history);

        $this->save($history);

        return $history;
    }

    public function createUnmatch(Request $request, Proxy $proxy, Round $round, bool $emailCopy): MatchingHistory
    {
        $history = MatchingHistory::createUnmatch($request, $proxy, $round, $emailCopy);

        $this->addMatcher($history);

        $this->save($history);

        return $history;
    }

    private function addMatcher(MatchingHistory $history): void
    {
        $user = $this->security->getUser();

        if ($user instanceof Adherent) {
            $history->matcher = $user;
        } elseif ($user instanceof Administrator) {
            $history->adminMatcher = $user;
        }
    }

    private function save(MatchingHistory $history): void
    {
        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
