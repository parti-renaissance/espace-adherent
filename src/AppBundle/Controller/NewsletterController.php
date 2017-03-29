<?php

namespace AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Form\NewsletterUnsubscribeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NewsletterController extends Controller
{
    /**
     * @Route("/newsletter", name="newsletter_subscription")
     * @Method({"GET", "POST"})
     */
    public function subscriptionAction(Request $request)
    {
        $subscription = new NewsletterSubscription();

        $form = $this->createForm(NewsletterSubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.newsletter_subscription.handler')->subscribe($subscription);

            return $this->redirectToRoute('newsletter_subscription_subscribed');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/merci", name="newsletter_subscription_subscribed")
     * @Method("GET")
     */
    public function subscribedAction()
    {
        return $this->render('newsletter/subscribed.html.twig');
    }

    /**
     * @Route("/newsletter/desinscription", name="newsletter_unsubscribe")
     * @Method({"GET", "POST"})
     */
    public function unsubscribeAction(Request $request)
    {
        $form = $this->createForm(NewsletterUnsubscribeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.newsletter_subscription.handler')->unsubscribe((string) $form->getData()['email']);

            return $this->redirectToRoute('newsletter_unsubscribe_unsubscribed');
        }

        return $this->render('newsletter/unsubscribe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/desinscription/desinscrit", name="newsletter_unsubscribe_unsubscribed")
     * @Method("GET")
     */
    public function unsubscribedAction()
    {
        return $this->render('newsletter/unsubscribed.html.twig');
    }
}
