<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterSubscriptionType;

class NewsletterController extends Controller
{
    /**
     * @Route("/newsletter", name="newsletter")
     */
    public function newsletterAction(Request $request)
    {
        $newsletter = new NewsletterSubscription();
        $form = $this->createForm(NewsletterSubscriptionType::class, $newsletter);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newsletter->setIdFromEmail($form->get('email')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newsletter);
            $em->flush();
            $request->getSession()->getFlashBag()->add('info', 'Email bien enregistré !');
        }

        return $this->render('newsletter/newsletter.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
