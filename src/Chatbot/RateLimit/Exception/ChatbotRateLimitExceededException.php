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

    private const UPSELL_TIERS = [
        ChatbotUserTier::Public,
        ChatbotUserTier::Contact,
        ChatbotUserTier::Sympathisant,
    ];

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
            'Le bot est victime de son succès : il a atteint sa capacité maximale pour aujourd\'hui. Ce n\'est pas vous, c\'est nous. Le service repart demain.',
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
            self::buildTierMessage($tier, $period, $limit),
        );
    }

    private static function buildTierMessage(ChatbotUserTier $tier, ChatbotRateLimitPeriod $period, int $limit): string
    {
        if (0 === $limit) {
            return \sprintf('L\'accès au chatbot n\'est pas autorisé pour votre profil (%s).', $tier->label());
        }

        return match ($period) {
            ChatbotRateLimitPeriod::Minute => 'Vous avez atteint votre limite de questions pour cette minute. Patientez quelques instants et reposez votre question.',
            ChatbotRateLimitPeriod::Hour => 'Vous avez atteint votre limite de questions pour cette heure. Le compteur repart bientôt, repassez d\'ici peu.',
            ChatbotRateLimitPeriod::Day => self::buildDayMessage($tier),
        };
    }

    private static function buildDayMessage(ChatbotUserTier $tier): string
    {
        if (\in_array($tier, self::UPSELL_TIERS, true)) {
            return 'Vous avez atteint votre limite de questions pour aujourd\'hui, le compteur repart demain. Envie d\'un accès plus large ? Les adhérents disposent d\'un quota nettement plus généreux.';
        }

        return 'Vous avez atteint votre limite de questions pour aujourd\'hui. Le compteur repart demain.';
    }
}
