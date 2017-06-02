<?php

namespace AppBundle\Controller;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestUtils;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Donation\DonationView;
use AppBundle\Entity\Donation;
use AppBundle\Form\DonationSubscriptionRequestType;
use AppBundle\Form\DonationRequestType;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    /**
     * @Route(defaults={"_enable_campaign_silence"=true}, name="donation_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->render('donation/index.html.twig', [
                'amount' => DonationRequest::DEFAULT_AMOUNT,
            ]);
        }

        if ($request->query->has('abonnement')) {
            return $this->redirectToRoute('donation_subscription', ['montant' => $amount]);
        }

        return $this->redirectToRoute('donation_informations', ['montant' => $amount]);
    }

    /**
     * @Route("/mensuel", name="donation_subscription")
     * @Method("GET|POST")
     */
    public function subscriptionAction(Request $request)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->redirectToRoute('donation_index');
        }

        $form = $this->createForm(DonationSubscriptionRequestType::class, [
            'duration' => PayboxPaymentSubscription::UNLIMITED,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('donation_informations', [
                'montant' => $amount,
                'abonnement' => $form->get('duration')->getData(),
            ]);
        }

        return $this->render('donation/subscription.html.twig', [
            'amount' => $amount,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/coordonnees", defaults={"_enable_campaign_silence"=true}, name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(Request $request)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->redirectToRoute('donation_index');
        }

        $subscription = $request->query->getInt('abonnement', PayboxPaymentSubscription::NONE);

        if (!PayboxPaymentSubscription::isValid($subscription)) {
            return $this->redirectToRoute('donation_subscription', ['montant' => $amount]);
        }

        $donationRequest = $this->get(DonationRequestUtils::class)
            ->createFromRequest($request, (float) $amount, $subscription, $this->getUser());

        $form = $this->createForm(DonationRequestType::class, $donationRequest, ['locale' => $request->getLocale()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.donation_request.handler')->handle($donationRequest);

            return $this->redirectToRoute('donation_pay', [
                'uuid' => $donationRequest->getUuid()->toString(),
            ]);
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => DonationView::createFromDonationRequest($donationRequest),
        ]);
    }

    /**
     * @Route(
     *     "/{uuid}/paiement",
     *     requirements={"uuid"="%pattern_uuid%"},
     *     defaults={"_enable_campaign_silence"=true},
     *     name="donation_pay"
     * )
     * @Method("GET")
     */
    public function payboxAction(Donation $donation)
    {
        if ($donation->isFinished()) {
            $this->get('app.membership_utils')->clearRegisteringDonation();

            return $this->redirectToRoute('donation_index');
        }

        $paybox = $this->get('app.donation.form_factory')->createPayboxFormForDonation($donation);

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback/{_callback_token}", defaults={"_enable_campaign_silence"=true}, name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request, $_callback_token)
    {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $this->get('app.donation.transaction_callback_handler')->handle($id, $request, $_callback_token);
    }

    /**
     * @Route(
     *     "/{uuid}/{status}",
     *     requirements={"status"="effectue|erreur", "uuid"="%pattern_uuid%"},
     *     defaults={"_enable_campaign_silence"=true},
     *     name="donation_result"
     * )
     * @Method("GET")
     */
    public function resultAction(Request $request, Donation $donation)
    {
        $retryUrl = null;
        if (!$donation->isSuccessful()) {
            $retryUrl = $this->generateUrl(
                'donation_informations',
                $this->get(DonationRequestUtils::class)->createRetryPayload($donation, $request)
            );
        }

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => DonationView::createFromDonation($donation),
            'retry_url' => $retryUrl,
            'is_in_subscription_process' => $this->get('app.membership_utils')->isInSubscriptionProcess(),
        ]);
    }
}
