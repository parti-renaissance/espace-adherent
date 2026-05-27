<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

use App\Chatbot\RateLimit\Exception\ChatbotRateLimitExceededException;
use App\Entity\Adherent;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

class ChatbotRateLimitChecker
{
    private const GLOBAL_BUCKET_KEY = 'chatbot_global';

    /** @var array<string, RateLimiterFactory> */
    private array $factories = [];

    public function __construct(
        private readonly ChatbotTierResolver $tierResolver,
        #[Autowire(service: 'cache.rate_limiter')]
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function check(Adherent $adherent, string $agent): void
    {
        $tier = $this->tierResolver->resolve($adherent);

        foreach (ChatbotRateLimitPeriod::cases() as $period) {
            if (0 === ChatbotRateLimitConfig::getGlobalLimit($period)) {
                throw ChatbotRateLimitExceededException::forGlobal($period, 1, 0);
            }

            if (0 === ChatbotRateLimitConfig::getTierLimit($tier, $period)) {
                throw ChatbotRateLimitExceededException::forTier($tier, $period, 1, 0);
            }
        }

        $userKey = $this->userKey($adherent, $agent);

        foreach (ChatbotRateLimitPeriod::cases() as $period) {
            $tierLimit = ChatbotRateLimitConfig::getTierLimit($tier, $period);
            if (null !== $tierLimit) {
                $rateLimit = $this->getFactory($this->tierId($tier, $period), $tierLimit, $period)
                    ->create($userKey)
                    ->consume(1);

                if (!$rateLimit->isAccepted()) {
                    throw ChatbotRateLimitExceededException::forTier($tier, $period, $this->retryAfter($rateLimit), $tierLimit);
                }
            }

            $globalLimit = ChatbotRateLimitConfig::getGlobalLimit($period);
            if (null !== $globalLimit) {
                $rateLimit = $this->getFactory($this->globalId($period), $globalLimit, $period)
                    ->create(self::GLOBAL_BUCKET_KEY)
                    ->consume(1);

                if (!$rateLimit->isAccepted()) {
                    throw ChatbotRateLimitExceededException::forGlobal($period, $this->retryAfter($rateLimit), $globalLimit);
                }
            }
        }
    }

    private function getFactory(string $id, int $limit, ChatbotRateLimitPeriod $period): RateLimiterFactory
    {
        if (!isset($this->factories[$id])) {
            $this->factories[$id] = new RateLimiterFactory(
                [
                    'id' => $id,
                    'policy' => 'sliding_window',
                    'limit' => $limit,
                    'interval' => $period->interval(),
                ],
                new CacheStorage($this->cache),
            );
        }

        return $this->factories[$id];
    }

    private function globalId(ChatbotRateLimitPeriod $period): string
    {
        return 'chatbot_global_'.$period->value;
    }

    private function tierId(ChatbotUserTier $tier, ChatbotRateLimitPeriod $period): string
    {
        return 'chatbot_tier_'.$tier->value.'_'.$period->value;
    }

    private function userKey(Adherent $adherent, string $agent): string
    {
        return $agent.'_'.$adherent->getUuid()->toRfc4122();
    }

    private function retryAfter(RateLimit $rateLimit): int
    {
        return max(1, $rateLimit->getRetryAfter()->getTimestamp() - time());
    }
}
