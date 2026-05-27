<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit\Exception;

use App\Chatbot\RateLimit\ChatbotRateLimitPeriod;
use App\Chatbot\RateLimit\ChatbotUserTier;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ChatbotRateLimitExceededException extends TooManyRequestsHttpException
{
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_USER = 'user';

    private function __construct(
        public readonly string $scope,
        public readonly ChatbotRateLimitPeriod $period,
        public readonly int $retryAfter,
        public readonly int $limit,
        public readonly ?ChatbotUserTier $tier,
        string $message,
    ) {
        parent::__construct(max(1, $retryAfter), $message);
    }

    public static function forGlobal(ChatbotRateLimitPeriod $period, int $retryAfter, int $limit): self
    {
        return new self(
            self::SCOPE_GLOBAL,
            $period,
            $retryAfter,
            $limit,
            null,
            \sprintf(
                '🚦 Le service est temporairement saturé. Réessayez dans %s.',
                self::formatRetryAfter($retryAfter)
            ),
        );
    }

    public static function forTier(
        ChatbotUserTier $tier,
        ChatbotRateLimitPeriod $period,
        int $retryAfter,
        int $limit,
    ): self {
        return new self(
            self::SCOPE_USER,
            $period,
            $retryAfter,
            $limit,
            $tier,
            self::buildTierMessage($tier, $period, $retryAfter, $limit),
        );
    }

    private static function buildTierMessage(
        ChatbotUserTier $tier,
        ChatbotRateLimitPeriod $period,
        int $retryAfter,
        int $limit,
    ): string {
        if (0 === $limit) {
            return \sprintf(
                '🔒 L\'accès au chatbot n\'est pas autorisé pour votre profil (%s).',
                $tier->label()
            );
        }

        return match ($period) {
            ChatbotRateLimitPeriod::Minute => \sprintf(
                '⏱️ Trop de questions ! Attendez %s pour reprendre. (Limite %s : %d/min)',
                self::formatRetryAfter($retryAfter),
                $tier->label(),
                $limit,
            ),
            ChatbotRateLimitPeriod::Hour => \sprintf(
                '🔄 Vous avez atteint votre quota horaire (%d/heure). Réessayez dans %s.',
                $limit,
                self::formatRetryAfter($retryAfter),
            ),
            ChatbotRateLimitPeriod::Day => \sprintf(
                '📅 Votre quota journalier (%d/jour) est épuisé. Réessayez dans %s.',
                $limit,
                self::formatRetryAfter($retryAfter),
            ),
        };
    }

    private static function formatRetryAfter(int $seconds): string
    {
        if ($seconds < 60) {
            return \sprintf('%d s', $seconds);
        }

        if ($seconds < 3600) {
            return \sprintf('%d min', (int) ceil($seconds / 60));
        }

        return \sprintf('%d h', (int) ceil($seconds / 3600));
    }
}
