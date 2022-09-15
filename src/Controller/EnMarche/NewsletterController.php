<?php

namespace App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\Entity\NewsletterSubscription;
use App\Form\NewsletterInvitationType;
use App\Form\NewsletterSubscriptionType;
use App\Form\NewsletterUnsubscribeType;
use App\Newsletter\Invitation;
use App\Newsletter\NewsletterInvitationHandler;
use App\Newsletter\NewsletterSubscriptionHandler;
use App\Repository\NewsletterSubscriptionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    /**
     * @Route("/newsletter", name="newsletter_subscription", methods={"GET", "POST"})
     */
    public function subscriptionAction(
        Request $request,
        GeoCoder $geoCoder,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        NewsletterSubscriptionHandler $newsletterSubscriptionHandler
    ): Response {
        return $this->redirectToRoute('homepage');

        if ($email = $request->query->get('mail')) {
            $subscription = $newsletterSubscriptionRepository->findOneNotConfirmedByEmail($email);
            if ($subscription) {
                $subscription->setCountry($geoCoder->getCountryCodeFromIp($request->getClientIp()));
            } else {
                $subscription = new NewsletterSubscription(
                    $email,
                    null,
                    $geoCoder->getCountryCodeFromIp($request->getClientIp())
                );
            }
        } elseif ($user = $this->getUser()) {
            $subscription = new NewsletterSubscription(
                $user->getEmailAddress(),
                $user->getPostalCode(),
                $user->getCountry()
            );
        } else {
            $subscription = new NewsletterSubscription(
                null,
                null,
                $geoCoder->getCountryCodeFromIp($request->getClientIp())
            );
        }

        $subscription->setRecaptcha($request->request->get('g-recaptcha-response'));
        $form = $this
            ->createForm(NewsletterSubscriptionType::class, $subscription)
            ->handleRequest($request)
        ;

        if ($request->request->has('g-recaptcha-response') && $form->isSubmitted() && $form->isValid()) {
            $newsletterSubscriptionHandler->subscribe($subscription);

            return $this->redirectToRoute('app_newsletter_subscription_mail_sent');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/confirmation/{uuid}/{confirm_token}", name="app_newsletter_confirmation", methods={"GET"})
     * @Entity("subscription", expr="repository.findOneNotConfirmedByUuidAndToken(uuid, confirm_token)")
     */
    public function subscriptionConfirmationAction(
        NewsletterSubscription $subscription,
        NewsletterSubscriptionHandler $newsletterSubscriptionHandler
    ): Response {
        $newsletterSubscriptionHandler->confirm($subscription);

        return $this->redirectToRoute('app_newsletter_subscription_subscribed');
    }

    /**
     * @Route("/newsletter/suscription-demandee", name="app_newsletter_subscription_mail_sent", methods={"GET"})
     */
    public function subscriptionMailSendedAction(): Response
    {
        return $this->render('newsletter/subscription_mail_sent.html.twig');
    }

    /**
     * @Route("/newsletter/merci", name="app_newsletter_subscription_subscribed", methods={"GET"})
     */
    public function subscribedAction(): Response
    {
        return $this->render('newsletter/subscribed.html.twig');
    }

    /**
     * @Route("/newsletter/desinscription", name="app_newsletter_unsubscribe", methods={"GET", "POST"})
     */
    public function unsubscribeAction(
        Request $request,
        NewsletterSubscriptionHandler $newsletterSubscriptionHandler
    ): Response {
        $form = $this->createForm(NewsletterUnsubscribeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletterSubscriptionHandler->unsubscribe((string) $form->getData()['email']);

            return $this->redirectToRoute('app_newsletter_unsubscribe_unsubscribed');
        }

        return $this->render('newsletter/unsubscribe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/desinscription/desinscrit", name="app_newsletter_unsubscribe_unsubscribed", methods={"GET"})
     */
    public function unsubscribedAction(): Response
    {
        return $this->render('newsletter/unsubscribed.html.twig');
    }

    /**
     * @Route("/newsletter/invitation", name="app_newsletter_invitation", methods={"GET", "POST"})
     */
    public function invitationAction(Request $request, NewsletterInvitationHandler $handler): Response
    {
        $form = $this->createForm(NewsletterInvitationType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Invitation $invitation */
            $invitation = $form->getData();
            $handler->handle($invitation, $request->getClientIp());
            $request->getSession()->set('newsletter_invitations_count', \count($invitation->guests));

            return $this->redirectToRoute('app_newsletter_invitation_sent');
        }

        return $this->render('newsletter/invitation.html.twig', [
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/invitation/merci", name="app_newsletter_invitation_sent", methods={"GET"})
     */
    public function invitationSentAction(Request $request): Response
    {
        if (!$invitationsCount = $request->getSession()->remove('newsletter_invitations_count')) {
            throw new PreconditionFailedHttpException('The invitations count is missing from the session.');
        }

        return $this->render('newsletter/invitation_sent.html.twig', [
            'invitations_count' => $invitationsCount,
        ]);
    }
}
