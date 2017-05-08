<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
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
        return $this->render('donation/index.html.twig', [
            'amount' => (float) $request->query->get('montant', 50),
        ]);
    }

    /**
     * @Route("/coordonnees", defaults={"_enable_campaign_silence"=true}, name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(Request $request)
    {
        $amount = (float) $request->query->get('montant');

        if (!$amount) {
            return $this->redirectToRoute('donation_index');
        }

        $factory = $this->get('app.donation_request.factory');
        $donationRequest = $factory->createFromRequest($request, $amount, $this->getUser());
        $form = $this->createForm(DonationRequestType::class, $donationRequest, ['locale' => $request->getLocale()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $donation = $this->get('app.donation_request.handler')->handle($donationRequest, $request->getClientIp());

            return $this->redirectToRoute('donation_pay', [
                'uuid' => $donation->getUuid()->toString(),
            ]);
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => $donationRequest,
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
     * @Route("/callback", defaults={"_enable_campaign_silence"=true}, name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request)
    {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $this->get('app.donation.transaction_callback_handler')->handle($id, $request);
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
        $parameters = [
            'montant' => $donation->getAmount() / 100,
            'ge' => $donation->getGender(),
            'ln' => $donation->getLastName(),
            'fn' => $donation->getFirstName(),
            'em' => urlencode($donation->getEmailAddress()),
            'co' => $donation->getCountry(),
            'pc' => $donation->getPostalCode(),
            'ci' => $donation->getCityName(),
            'ad' => urlencode($donation->getAddress()),
        ];

        if ($donation->getPhone()) {
            $parameters['phc'] = $donation->getPhone()->getCountryCode();
            $parameters['phn'] = $donation->getPhone()->getNationalNumber();
        }

        $retryUrl = $this->generateUrl('donation_informations', $parameters);

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
            'is_in_subscription_process' => $this->get('app.membership_utils')->isInSubscriptionProcess(),
        ]);
    }
}
