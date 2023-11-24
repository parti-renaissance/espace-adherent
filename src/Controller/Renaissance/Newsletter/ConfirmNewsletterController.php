<?php

namespace App\Controller\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(path: '/newsletter/confirmation/{uuid}/{confirm_token}', name: 'app_renaissance_newsletter_confirm', methods: ['GET'])]
class ConfirmNewsletterController extends AbstractController
{
    #[Entity('subscription', expr: 'repository.findOneByUuidAndToken(uuid, confirm_token)')]
    public function __invoke(
        NewsletterSubscription $subscription,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        if (!$subscription->confirmedAt) {
            $subscription->confirmedAt = new \DateTime();
            $entityManager->flush();

            $eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::CONFIRMATION);
        }

        $this->addFlash('success', 'Votre inscription à la newsletter a bien été confirmée.');

        return $this->redirectToRoute('app_renaissance_adherent_space');
    }
}
