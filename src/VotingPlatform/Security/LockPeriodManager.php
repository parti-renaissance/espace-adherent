<?php

namespace App\VotingPlatform\Security;

use App\Entity\Adherent;
use App\Repository\VotingPlatform\VoteRepository;
use App\VotingPlatform\Election\Listener\LockPeriodClearCacheListener;
use Psr\SimpleCache\CacheInterface;

class LockPeriodManager
{
    private const CACHE_PREFIX_PATTERN = 'voting_platform_%d_voted';

    private $cache;
    private $voteRepository;

    public function __construct(VoteRepository $voteRepository, CacheInterface $votingPlatformCache)
    {
        $this->cache = $votingPlatformCache;
        $this->voteRepository = $voteRepository;
    }

    /**
     * Returns true if the adherent has voted during the last 3 months
     */
    public function isLocked(Adherent $adherent): bool
    {
        $voteDate = null;
        $cacheKey = $this->getCacheKey($adherent->getId());

        if (!$this->cache->has($cacheKey)) {
            $vote = $this->voteRepository->findLastForAdherent($adherent);

            /**
             * Store vote date if found, null otherwise for 1 month. If vote was not found
             * and `null` value was saved in the cache, then when adherent will vote, we should remove this item!
             *
             * @see LockPeriodClearCacheListener
             */
            $this->cache->set($cacheKey, $vote ? $voteDate = $vote->getVotedAt() : null, 2678400);
        } else {
            $voteDate = $this->cache->get($cacheKey);
        }

        return $voteDate && $voteDate->diff(new \DateTime())->m < 3;
    }

    public function clearForAdherent(Adherent $adherent): void
    {
        $this->cache->delete($this->getCacheKey($adherent->getId()));
    }

    private function getCacheKey(int $adherentId): string
    {
        return sprintf(self::CACHE_PREFIX_PATTERN, $adherentId);
    }
}
