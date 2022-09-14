<?php

namespace App\Controller\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/newsletter/confirmation/{uuid}/{confirm_token}", name="app_renaissance_newsletter_confirm", methods={"GET"})
 */
class ConfirmNewsletterController extends AbstractController
{
    /**
     * @Entity("subscription", expr="repository.findOneByUuidAndToken(uuid, confirm_token)")
     */
    public function __invoke(NewsletterSubscription $subscription, EntityManagerInterface $entityManager): Response
    {
        if (!$subscription->confirmedAt) {
            $subscription->confirmedAt = new \DateTime();
            $entityManager->flush();
        }

        $this->addFlash('success', 'Votre inscription à la newsletter a bien été confirmée.');

        return $this->redirectToRoute('app_renaissance_homepage');
    }
}
