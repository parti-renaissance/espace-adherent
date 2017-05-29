<?php

namespace AppBundle\Controller;

use AppBundle\Notification\SendOnArticlePublished;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FirebaseController extends Controller
{
    /**
     * @Route("/firebase-messaging-sw.js")
     * @Method("GET")
     */
    public function firebaseServiceWorkerAction(): Response
    {
        return new Response(
            $this->renderView('firebase/firebase-messaging-ws.js.twig'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/javascript']
        );
    }

    /**
     * @Route("/api/firebase-token")
     * @Method("POST")
     */
    public function firebaseTokenAction(Request $request): Response
    {
        $this->get('app.firebase.cloud_messaging')->addTopicSubscription(
            SendOnArticlePublished::TOPIC,
            $request->get('token')
        );

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
