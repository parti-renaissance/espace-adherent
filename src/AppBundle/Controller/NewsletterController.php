<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterSubscriptionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/newsletter")
 */
class NewsletterController extends Controller
{
    /**
     * @Route("", name="newsletter_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $newsletter = new NewsletterSubscription();

        $form = $this->createForm(NewsletterSubscriptionType::class, $newsletter);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletter->setId(NewsletterSubscription::createUuid($newsletter->getEmail()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($newsletter);
            $em->flush();

            return $this->redirectToRoute('newsletter_thanks');
        }

        return $this->render('newsletter/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remerciement", name="newsletter_thanks")
     * @Method("GET")
     */
    public function thanksAction()
    {
        return $this->render('newsletter/thanks.html.twig');
    }
}
