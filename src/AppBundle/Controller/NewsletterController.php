<?php

namespace AppBundle\Controller;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterSubscriptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.newsletter_subscription.handler')->handle($subscription, $request);

            return $this->render('newsletter/subscribed.html.twig');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
