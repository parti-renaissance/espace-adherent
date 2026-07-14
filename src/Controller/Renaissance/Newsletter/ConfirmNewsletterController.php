<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Newsletter;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use App\Repository\Renaissance\NewsletterSourceRepository;
use App\Repository\Renaissance\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ConfirmNewsletterController extends AbstractController
{
    private PostHogService $postHog;
    private HashEmailService $hashEmail;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    #[Required]
    public function setHashEmailService(HashEmailService $hashEmail): void
    {
        $this->hashEmail = $hashEmail;
    }

    public function __invoke(
        Request $request,
        string $uuid,
        string $confirm_token,
        NewsletterSubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        NewsletterSourceRepository $newsletterSourceRepository,
    ): Response {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->render('renaissance/newsletter/confirm.html.twig', [
                'uuid' => $uuid,
                'confirm_token' => $confirm_token,
            ]);
        }

        $subscription = $subscriptionRepository->findOneByUuidAndToken($uuid, $confirm_token);

        if (!$subscription) {
            throw $this->createNotFoundException();
        }

        if (!$subscription->confirmedAt) {
            $subscription->confirmedAt = new \DateTime();
            $entityManager->flush();

            $eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::CONFIRMATION);

            $this->postHog->captureServerSideWithSet(
                PostHogEventName::NEWSLETTER_CONFIRMED_SERVER,
                [],
                ['email' => $subscription->getEmail()],
                $this->hashEmail->hash($subscription->getEmail()),
            );
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
