<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterInvitationType;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Form\NewsletterUnsubscribeType;
use AppBundle\Newsletter\Invitation;
use AppBundle\Newsletter\NewsletterSubscriptionHandler;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends Controller
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

        $form = $this
            ->createForm(NewsletterSubscriptionType::class, $subscription)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletterSubscriptionHandler->subscribe($subscription);

            return $this->redirectToRoute('app_newsletter_subscription_mail_sent');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/confirmation/{uuid}/{token}", name="app_newsletter_confirmation", methods={"GET"})
     * @Entity("subscription", expr="repository.findOneNotConfirmedByUuidAndToken(uuid, token)")
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
    public function invitationAction(Request $request): Response
    {
        $form = $this->createForm(NewsletterInvitationType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Invitation $invitation */
            $invitation = $form->getData();
            $this->get('app.newsletter_invitation.handler')->handle($invitation, $request->getClientIp());
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
