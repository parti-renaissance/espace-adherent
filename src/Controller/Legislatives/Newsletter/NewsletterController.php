<?php

namespace App\Controller\Legislatives\Newsletter;

use App\Entity\LegislativeNewsletterSubscription;
use App\Legislative\Newsletter\LegislativeNewsletterSubscriptionHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/newsletters")
 */
class NewsletterController extends AbstractController
{
    /**
     * @Route(
     *     "/confirmation/{uuid}/{validation_token}",
     *     name="app_legislatives_newsletter_confirmation",
     *     methods={"GET"},
     *     requirements={"uuid": "%pattern_uuid%", "validation_token": "%pattern_uuid%"}
     * )
     * @Entity("subscription", expr="repository.findOneNotConfirmedByUuidAndToken(uuid, validation_token)")
     */
    public function newsletterConfirmation(
        LegislativeNewsletterSubscription $subscription,
        LegislativeNewsletterSubscriptionHandler $legislativeNewsletterSubscriptionHandler
    ): Response {
        $legislativeNewsletterSubscriptionHandler->confirm($subscription);

        return $this->redirectToRoute('app_legislatives_newsletter_thank');
    }

    /**
     * @Route("/merci", name="app_legislatives_newsletter_thank", methods={"GET"})
     */
    public function subscribedThanks(): Response
    {
        return $this->render('legislatives/newsletter/thanks.html.twig');
    }
}
