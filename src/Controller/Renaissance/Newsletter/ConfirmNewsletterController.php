<?php

namespace App\Controller\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use App\Newsletter\NewsletterTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        if (NewsletterTypeEnum::SITE_EU === $subscription->source) {
            return $this->render('besoindeurope/national_event/newsletter_confirmation.html.twig');
        }

        $this->addFlash('success', 'Votre inscription à la newsletter a bien été confirmée.');

        return $this->redirectToRoute('app_renaissance_adherent_space');
    }
}
