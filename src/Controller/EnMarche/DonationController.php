<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestUtils;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Donation\PayboxPaymentUnsubscription;
use AppBundle\Entity\Donation;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Exception\InvalidPayboxPaymentSubscriptionValueException;
use AppBundle\Form\DonationRequestType;
use AppBundle\Form\ConfirmActionType;
use AppBundle\Membership\MembershipRegistrationProcess;
use AppBundle\Repository\DonationRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->redirectToRoute('donation_informations', [
            'montant' => $amount,
            'abonnement' => $request->query->has('abonnement') ? // Force unlimited subscription if needed
                PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE,
        ]);
    }

    /**
     * @Route("/coordonnees", defaults={"_enable_campaign_silence"=true}, name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(Request $request, DonationRequestUtils $donationRequestUtils)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->redirectToRoute('donation_index');
        }

        try {
            $form = $this->createForm(DonationRequestType::class, null, ['locale' => $request->getLocale()]);
            /** @var DonationRequest $donationRequest */
            $donationRequest = $form->handleRequest($request)->getData();
        } catch (InvalidPayboxPaymentSubscriptionValueException $e) {
            return $this->redirectToRoute('donation_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.donation_request.handler')->handle($donationRequest);
            $donationRequestUtils->terminateDonationRequest();

            return $this->redirectToRoute('donation_pay', [
                'uuid' => $donationRequest->getUuid()->toString(),
            ]);
        }

        $donationRequestUtils->startDonationRequest($donationRequest);

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
    public function resultAction(Request $request, Donation $donation, MembershipRegistrationProcess $membershipRegistrationProcess)
    {
        $retryUrl = null;
        if (!$donation->isSuccessful()) {
            $retryUrl = $this->generateUrl(
                'donation_informations',
                $this->get(DonationRequestUtils::class)->createRetryPayload($donation, $request)
            );
        }

        $membershipRegistrationProcess->terminate();

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
            'is_new_adherent' => $request->query->get('is_new_adherent'),
        ]);
    }

    /**
     * @Route("/mensuel/annuler", name="donation_subscription_cancel")
     * @Method({"GET", "POST"})
     */
    public function cancelSubscriptionAction(Request $request, DonationRepository $donationRepository, PayboxPaymentUnsubscription $payboxPaymentUnsubscription, LoggerInterface $logger): Response
    {
        $donations = $donationRepository->findAllSubscribedDonationByEmail($this->getUser()->getEmailAddress());

        if (!$donations) {
            $this->addFlash(
                'danger',
                'Aucun don mensuel n\'a été trouvé'
            );

            return $this->redirect($this->generateUrl('app_user_profile_donation'));
        }

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('allow')->isClicked()) {
                foreach ($donations as $donation) {
                    try {
                        $payboxPaymentUnsubscription->unsubscribe($donation);
                        $this->getDoctrine()->getManager()->flush();
                        $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $this->getUser());
                        $this->addFlash(
                            'success',
                            'Votre don mensuel a bien été annulé. Vous recevrez bientôt un mail de confirmation.'
                        );
                        $logger->info(sprintf('Subscription donation id(%d) from user email %s have been cancel successfully.', $donation->getId(), $this->getUser()->getEmailAddress()));
                    } catch (PayboxPaymentUnsubscriptionException $e) {
                        $this->addFlash('danger', 'La requête n\'a pas abouti, veuillez réessayer s\'il vous plait. Si le problème persiste, merci de nous contacter en <a href="https://contact.en-marche.fr/" target="_blank">cliquant ici</a>');
                        $this->addFlash('danger', $e->getCodeError());

                        $logger->error(sprintf('Subscription donation id(%d) from user email %s have an error.', $donation->getId(), $this->getUser()->getEmailAddress()), ['exception' => $e]);
                    }
                }
            }

            return $this->redirect($this->generateUrl('app_user_profile_donation'));
        }

        return $this->render('user/donation_subscription_cancel_confirmation.html.twig', [
            'confirmation_form' => $form->createView(),
        ]);
    }
}
