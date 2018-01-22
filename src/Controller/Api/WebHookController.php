<?php

namespace AppBundle\Controller\Api;

use AppBundle\WebHook\Event;
use AppBundle\WebHook\WebHookManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebHookController extends AbstractController
{
    /**
     * @Route("/webhooks/{event}", name="app_webhook_list_config")
     * @Security("is_granted('ROLE_OAUTH_SCOPE_WEB_HOOK')")
     */
    public function listConfigAction(string $event, WebHookManager $manager, Serializer $serializer): JsonResponse
    {
        if (!Event::isValid($event)) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $serializer->serialize(
                $manager->getOrCreateWebHook(new Event($event)),
                'json',
                SerializationContext::create()->setGroups(['api'])
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
