<?php

declare(strict_types=1);

namespace App\Controller\Api\Chatbot;

use App\Chatbot\ChatbotManager;
use App\Entity\Adherent;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CANARY_TESTER')]
#[Route('/v3/ai/chat', methods: ['POST'])]
class PostMessageController extends AbstractController
{
    public function __construct(
        private readonly AgentInterface $geminiAgent,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, ChatbotManager $chatbotManager, #[CurrentUser] Adherent $user): StreamedResponse
    {
        try {
            $data = $request->toArray();
            $message = isset($data['message']) && \is_string($data['message']) ? trim($data['message']) : '';
            $threadId = isset($data['thread_id']) && \is_string($data['thread_id']) ? $data['thread_id'] : null;
        } catch (\Throwable) {
            throw new BadRequestHttpException('JSON invalide');
        }

        if ('' === $message) {
            throw new BadRequestHttpException('Aucun message');
        }

        $thread = $chatbotManager->handleUserMessage($message, $threadId, $user);
        $messageBag = $chatbotManager->buildContextMessageBag($thread);

        return new StreamedResponse(function () use ($messageBag, $thread, $chatbotManager) {
            set_time_limit(0);

            $fullResponse = '';

            try {
                $result = $this->geminiAgent->call($messageBag);
                $content = $result->getContent();

                foreach (is_iterable($content) ? $content : [$content] as $content) {
                    if (connection_aborted()) {
                        break;
                    }

                    if (empty($content)) {
                        continue;
                    }

                    $fullResponse .= $content;

                    echo 'data: '.json_encode($content, \JSON_UNESCAPED_UNICODE)."\n\n";

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
            'X-Chatbot-Thread-UUID' => $thread->getUuid()->toString(),
        ]);
    }
}
