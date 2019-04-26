<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Form\NewsletterInvitationType;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Form\NewsletterUnsubscribeType;
use AppBundle\Newsletter\Invitation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends Controller
{
    /**
     * @Route("/newsletter", name="newsletter_subscription")
     * @Method({"GET", "POST"})
     */
    public function subscriptionAction(Request $request, GeoCoder $geoCoder)
    {
        if ($request->query->has('mail')) {
            $subscription = new NewsletterSubscription(
                $request->query->get('mail'),
                null,
                $geoCoder->getCountryCodeFromIp($request->getClientIp())
            );
        } elseif ($user = $this->getUser()) {
            $subscription = new NewsletterSubscription(
                $user->getEmailAddress(),
                $user->getPostalCode(),
                $user->getCountry()
            );
        } else {
            $subscription = new NewsletterSubscription(null, null, $geoCoder->getCountryCodeFromIp($request->getClientIp()));
        }

        $form = $this
            ->createForm(NewsletterSubscriptionType::class, $subscription)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.newsletter_subscription.handler')->subscribe($subscription);

            return $this->redirectToRoute('newsletter_subscription_subscribed');
        }

        return $this->render('newsletter/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/merci", name="newsletter_subscription_subscribed")
     * @Method("GET")
     */
    public function subscribedAction()
    {
        return $this->render('newsletter/subscribed.html.twig');
    }

    /**
     * @Route("/newsletter/desinscription", name="newsletter_unsubscribe")
     * @Method({"GET", "POST"})
     */
    public function unsubscribeAction(Request $request)
    {
        $form = $this->createForm(NewsletterUnsubscribeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.newsletter_subscription.handler')->unsubscribe((string) $form->getData()['email']);

            return $this->redirectToRoute('newsletter_unsubscribe_unsubscribed');
        }

        return $this->render('newsletter/unsubscribe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/desinscription/desinscrit", name="newsletter_unsubscribe_unsubscribed")
     * @Method("GET")
     */
    public function unsubscribedAction()
    {
        return $this->render('newsletter/unsubscribed.html.twig');
    }

    /**
     * @Route("/newsletter/invitation", name="newsletter_invitation")
     * @Method("GET|POST")
     */
    public function invitationAction(Request $request)
    {
        $form = $this->createForm(NewsletterInvitationType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Invitation $invitation */
            $invitation = $form->getData();
            $this->get('app.newsletter_invitation.handler')->handle($invitation, $request->getClientIp());
            $request->getSession()->set('newsletter_invitations_count', \count($invitation->guests));

            return $this->redirectToRoute('newsletter_invitation_sent');
        }

        return $this->render('newsletter/invitation.html.twig', [
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/invitation/merci", name="newsletter_invitation_sent")
     * @Method("GET")
     */
    public function invitationSentAction(Request $request)
    {
        if (!$invitationsCount = $request->getSession()->remove('newsletter_invitations_count')) {
            throw new PreconditionFailedHttpException('The invitations count is missing from the session.');
        }

        return $this->render('newsletter/invitation_sent.html.twig', [
            'invitations_count' => $invitationsCount,
        ]);
    }
}
