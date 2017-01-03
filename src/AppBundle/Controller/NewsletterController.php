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
     * @Route("/newsletter/souscription", name="newsletter_subscription")
     * @Method({"GET", "POST"})
     */
    public function subscriptionAction(Request $request)
    {
        $subscription = new NewsletterSubscription();

        $form = $this->createForm(NewsletterSubscriptionType::class, $subscription);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subscription->setId(NewsletterSubscription::createUuid($subscription->getEmail()));
            $subscription->setClientIp($request->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($subscription);
            $em->flush();

            return $this->redirectToRoute('newsletter_subscribed');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/souscription-reussie", name="newsletter_subscribed")
     * @Method("GET")
     */
    public function subscribedAction()
    {
        return $this->render('newsletter/subscribed.html.twig');
    }
}
