<?php

declare(strict_types=1);

namespace App\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\ProcurationV2\MatchingHistory;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
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
