<?php

declare(strict_types=1);

namespace App\Controller\Api\Chatbot;

use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CANARY_TESTER')]
#[Route('/v3/chatbot/start', methods: ['POST'])]
class NewChatController extends AbstractController
{
    public function __construct(
        private readonly AgentInterface $geminiAgent,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request): StreamedResponse
    {
        try {
            $data = $request->toArray();
            $messagesData = $data['messages'] ?? [];
        } catch (\Throwable) {
            throw new BadRequestHttpException('JSON invalide');
        }

        if (empty($messagesData)) {
            throw new BadRequestHttpException('Aucun message');
        }

        $messageBag = new MessageBag();

        foreach ($messagesData as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';

            if ('user' === $role) {
                $messageBag->add(Message::ofUser($content));
            } elseif ('assistant' === $role || 'system' === $role) {
                $messageBag->add(Message::ofAssistant($content));
            }
        }

        return new StreamedResponse(function () use ($messageBag) {
            set_time_limit(0);

            try {
                $result = $this->geminiAgent->call($messageBag);

                $iterator = is_iterable($result) ? $result : [$result];

                foreach ($iterator as $chunk) {
                    if (connection_aborted()) {
                        break;
                    }

                    $content = $chunk->getContent();

                    if (empty($content)) {
                        continue;
                    }

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
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'x-vercel-ai-ui-message-stream' => 'v1',
            'Connection' => 'keep-alive',
        ]);
    }
}
