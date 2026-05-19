<?php

declare(strict_types=1);

namespace App\Chatbot\Antiseche;

use App\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class AntisecheRequestGuard
{
    private const MAX_MESSAGE_LENGTH = 4000;

    public function __construct(
        private readonly RateLimiterFactory $botAntisecheLimiter,
    ) {
    }

    public function enforceRateLimit(Adherent $user): void
    {
        $limit = $this->botAntisecheLimiter->create('antiseche_'.$user->getUuid()->toRfc4122())->consume(1);
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(max(1, $limit->getRetryAfter()->getTimestamp() - time()));
        }
    }

    /** @return array{0: string, 1: string|null} */
    public function parseAndValidatePayload(Request $request): array
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable) {
            throw new BadRequestHttpException('JSON invalide');
        }

        $message = isset($data['message']) && \is_string($data['message']) ? trim($data['message']) : '';
        $threadId = isset($data['thread_id']) && \is_string($data['thread_id']) ? $data['thread_id'] : null;

        if ('' === $message) {
            throw new BadRequestHttpException('Aucun message');
        }
        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new BadRequestHttpException(\sprintf('Message trop long (max %d caractères).', self::MAX_MESSAGE_LENGTH));
        }

        return [$message, $threadId];
    }
}
