<?php

namespace App\Controller\Renaissance;

use App\Controller\CanaryControllerTrait;
use App\OpenAI\ChatbotClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/chatbot/{code}', name: 'api_chatbot')]
class ChatbotController extends AbstractController
{
    use CanaryControllerTrait;

    #[Route(name: '_post', methods: ['POST'])]
    public function addMessageAction(
        Request $request,
        ChatbotClient $chatbotClient,
        string $code
    ): JsonResponse {
        $this->disableInProduction();

        $body = json_decode($request->getContent(), true);
        $message = $body['content'] ?? null;

        if (!$message) {
            throw new BadRequestHttpException('Missing "content" key in request body.');
        }

        $chatbotClient->addMessage($code, $message);

        return $this->json(['OK']);
    }

    #[Route(name: '_get', methods: ['GET'])]
    public function getThreadAction(
        ChatbotClient $chatbotClient,
        string $code
    ): JsonResponse {
        $this->disableInProduction();

        $thread = $chatbotClient->getCurrentThread($code);

        return $this->json(
            $thread,
            Response::HTTP_OK,
            [],
            ['groups' => ['chatbot_read']]
        );
    }

    #[Route(name: '_delete', methods: ['DELETE'])]
    public function clearAction(
        ChatbotClient $chatbotClient,
        string $code
    ): JsonResponse {
        $this->disableInProduction();

        $chatbotClient->clear($code);

        return $this->json(['OK']);
    }
}
