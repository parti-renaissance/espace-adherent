<?php

declare(strict_types=1);

namespace App\Controller\Api\Chatbot;

use App\Chatbot\Antiseche\AntisecheClient;
use App\Chatbot\Antiseche\AntisecheRequestGuard;
use App\Chatbot\Antiseche\AntisecheSseEventParser;
use App\Chatbot\Antiseche\Exception\AntisecheException;
use App\Chatbot\ChatbotManager;
use App\Entity\Adherent;
use App\Entity\Chatbot\Thread;
use App\Scope\AuthorizationChecker;
use App\Scope\FeatureEnum;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/ai/bot/stream', methods: ['POST'])]
class PostAntisecheStreamMessageController extends AbstractController
{
    public function __construct(
        private readonly AntisecheClient $antisecheClient,
        private readonly AntisecheRequestGuard $requestGuard,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        Request $request,
        ChatbotManager $chatbotManager,
        #[CurrentUser] Adherent $user,
    ): Response {
        if (!$this->authorizationChecker->isFeatureGranted($request, $user, [FeatureEnum::CHATBOT])) {
            throw $this->createAccessDeniedException();
        }

        $this->requestGuard->enforceRateLimit($user);

        [$message, $threadId] = $this->requestGuard->parseAndValidatePayload($request);

        $thread = $chatbotManager->handleUserMessage($message, $threadId, $user);

        try {
            $chunks = $this->antisecheClient->openStream($message, $chatbotManager->buildHistoryMessageBag($thread));
        } catch (AntisecheException $exception) {
            return $this->onUpstreamFailure($exception, $user, $thread);
        }

        return $this->buildStreamResponse($chunks, $thread, $chatbotManager);
    }

    private function onUpstreamFailure(AntisecheException $exception, Adherent $user, Thread $thread): JsonResponse
    {
        $this->logger->error('[CHATBOT antiseche] stream upstream failure', [
            'adherent' => $user->getUuid()->toRfc4122(),
            'thread' => $thread->getUuid()->toRfc4122(),
            'status' => $exception->statusCode,
            'message' => $exception->getMessage(),
        ]);

        return new JsonResponse(
            ['error' => 'Le service antiseche est temporairement indisponible.'],
            502,
        );
    }

    /** @param iterable<string> $chunks */
    private function buildStreamResponse(iterable $chunks, Thread $thread, ChatbotManager $chatbotManager): StreamedResponse
    {
        $response = new StreamedResponse($this->buildStreamCallback($chunks, $thread, $chatbotManager));

        $response->headers->set('Content-Type', 'text/event-stream; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('X-Chatbot-Thread-UUID', $thread->getUuid()->toRfc4122());

        return $response;
    }

    /** @param iterable<string> $chunks */
    private function buildStreamCallback(iterable $chunks, Thread $thread, ChatbotManager $chatbotManager): \Closure
    {
        return function () use ($chunks, $thread, $chatbotManager): void {
            set_time_limit(0);

            $parser = new AntisecheSseEventParser();
            $finalReply = null;

            foreach ($chunks as $chunk) {
                if (connection_aborted()) {
                    break;
                }

                $this->emitChunk($chunk);
                $parser->append($chunk);

                foreach ($parser->drainEvents() as $event) {
                    if ('done' === $event['event'] && \is_string($event['data']['reply'] ?? null)) {
                        $finalReply = $event['data']['reply'];
                    }
                }
            }

            if (null !== $finalReply) {
                $this->persistReply($chatbotManager, $thread, $finalReply);
            }
        };
    }

    private function emitChunk(string $chunk): void
    {
        echo $chunk;
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    private function persistReply(ChatbotManager $chatbotManager, Thread $thread, string $reply): void
    {
        try {
            $chatbotManager->handleBotResponse($thread, $reply);
        } catch (\Throwable $exception) {
            $this->logger->error('[CHATBOT antiseche] failed to persist reply', [
                'thread' => $thread->getUuid()->toRfc4122(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
