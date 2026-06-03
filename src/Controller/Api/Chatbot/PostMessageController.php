<?php

declare(strict_types=1);

namespace App\Controller\Api\Chatbot;

use App\Chatbot\ChatbotManager;
use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use App\Scope\FeatureEnum;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/ai/chat', methods: ['POST'])]
class PostMessageController extends AbstractController
{
    private const MAX_MESSAGE_LENGTH = 4000;

    public function __construct(
        #[AutowireLocator('ai.agent', indexAttribute: 'name')]
        private readonly ContainerInterface $agents,
        private readonly RateLimiterFactory $botChatbotLimiter,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, ChatbotManager $chatbotManager, #[CurrentUser] Adherent $user): StreamedResponse
    {
        try {
            $data = $request->toArray();
            $message = isset($data['message']) && \is_string($data['message']) ? trim($data['message']) : '';
            $threadId = isset($data['thread_id']) && \is_string($data['thread_id']) ? $data['thread_id'] : null;
            $agentId = isset($data['agent_id']) && \is_string($data['agent_id']) ? $data['agent_id'] : 'chatbot';
        } catch (\Throwable) {
            throw new BadRequestHttpException('JSON invalide');
        }

        if ('' === $message) {
            throw new BadRequestHttpException('Aucun message');
        }

        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new BadRequestHttpException(\sprintf('Message trop long (max %d caractères).', self::MAX_MESSAGE_LENGTH));
        }

        if (!$this->agents->has($agentId)) {
            throw new BadRequestHttpException('agent_id manquant ou invalide.');
        }

        $agentFeature = FeatureEnum::getFeatureForAgentId($agentId);
        if (!$agentFeature || !$this->authorizationChecker->isFeatureGranted($request, $user, [$agentFeature])) {
            throw $this->createAccessDeniedException();
        }

        $limit = $this->botChatbotLimiter->create('chatbot_'.$agentId.'_'.$user->getUuid()->toRfc4122())->consume(1);
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(max(1, $limit->getRetryAfter()->getTimestamp() - time()));
        }

        $agent = $this->agents->get($agentId);
        $thread = $chatbotManager->handleUserMessage($message, $threadId, $user, $agentId);
        $messageBag = $chatbotManager->buildContextMessageBag($thread);

        return new StreamedResponse(function () use ($agent, $messageBag, $thread, $chatbotManager) {
            set_time_limit(0);

            $fullResponse = '';

            try {
                $result = $agent->call($messageBag);
                $content = $result->getContent();

                foreach (is_iterable($content) ? $content : [$content] as $chunk) {
                    if (connection_aborted()) {
                        break;
                    }

                    if ($chunk instanceof TextDelta) {
                        $text = $chunk->getText();
                    } elseif (\is_string($chunk)) {
                        $text = $chunk;
                    } else {
                        continue;
                    }

                    if ('' === $text) {
                        continue;
                    }

                    $fullResponse .= $text;

                    echo 'data: '.json_encode($text, \JSON_UNESCAPED_UNICODE)."\n\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            } catch (\Throwable $e) {
                $this->logger->error('[CHATBOT ERROR] '.$e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);

                $errorPayload = json_encode(['error' => $e->getMessage()], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE);

                echo 'data: '.$errorPayload."\n\n";

                flush();
            } finally {
                if ($thread && !empty($fullResponse)) {
                    try {
                        $chatbotManager->handleBotResponse($thread, $fullResponse);
                    } catch (\Throwable $e) {
                        $this->logger->error('[CHATBOT] Failed to persist bot response', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'x-vercel-ai-ui-message-stream' => 'v1',
            'Connection' => 'keep-alive',
            'X-Chatbot-Thread-UUID' => $thread->getUuid()->toRfc4122(),
        ]);
    }
}
