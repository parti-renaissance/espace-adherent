<?php

namespace App\Controller\Renaissance;

use App\Chatbot\WebConversationManager;
use App\Controller\CanaryControllerTrait;
use App\Entity\Chatbot\Chatbot;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/chatbot/{code}', name: 'api_chatbot_')]
#[Entity('chatbot', expr: 'repository.findOneEnabledByCode(code)')]
class ChatbotController extends AbstractController
{
    use CanaryControllerTrait;

    #[Route(name: 'post', methods: ['POST'])]
    public function addMessageAction(
        Request $request,
        WebConversationManager $manager,
        Chatbot $chatbot
    ): JsonResponse {
        $this->disableInProduction();

        $body = json_decode($request->getContent(), true);
        $message = $body['content'] ?? null;

        if (!$message) {
            throw new BadRequestHttpException('Missing "content" key in request body.');
        }

        $thread = $manager->getCurrentThread($chatbot);

        $manager->addMessage($thread, $message);

        return $this->json(['OK']);
    }

    #[Route(name: 'get', methods: ['GET'])]
    public function getThreadAction(
        WebConversationManager $manager,
        Chatbot $chatbot
    ): JsonResponse {
        $this->disableInProduction();

        $thread = $manager->getCurrentThread($chatbot);

        return $this->json(
            $thread,
            Response::HTTP_OK,
            [],
            ['groups' => ['chatbot:read']]
        );
    }

    #[Route(name: 'delete', methods: ['DELETE'])]
    public function clearAction(
        WebConversationManager $manager,
        Chatbot $chatbot
    ): JsonResponse {
        $this->disableInProduction();

        $manager->end($chatbot);

        return $this->json(['OK']);
    }
}
