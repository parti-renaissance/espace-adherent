<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use App\Repository\Renaissance\NewsletterSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfirmNewsletterController extends AbstractController
{
    public function __invoke(
        #[MapEntity(expr: 'repository.findOneByUuidAndToken(uuid, confirm_token)')]
        NewsletterSubscription $subscription,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        NewsletterSourceRepository $newsletterSourceRepository,
    ): Response {
        if (!$subscription->confirmedAt) {
            $subscription->confirmedAt = new \DateTime();
            $entityManager->flush();

            $eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::CONFIRMATION);
        }

        if ($subscription->source) {
            $source = $newsletterSourceRepository->findOneByCode($subscription->source);

            if ($source && $source->confirmationRedirectUrl) {
                return $this->redirect($source->confirmationRedirectUrl);
            }
        }

        $this->addFlash('success', 'Votre inscription à la newsletter a bien été confirmée.');

        return $this->redirectToRoute('vox_app_redirect');
    }
}
