<?php

namespace App\Controller\Api;

use App\Repository\WebHookRepository;
use App\WebHook\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WebHookController extends AbstractController
{
    #[Route(path: '/webhooks/{event}', name: 'app_webhook_list_config')]
    #[IsGranted('ROLE_OAUTH_SCOPE_WEB_HOOK')]
    public function listConfigAction(string $event, WebHookRepository $repository): JsonResponse
    {
        if (!Event::isValid($event)) {
            throw $this->createNotFoundException();
        }

        $callbacks = $repository->findCallbacksByEvent(new Event($event));

        return $this->json([
            'event' => $event,
            'callbacks' => $callbacks,
        ]);
    }
}
