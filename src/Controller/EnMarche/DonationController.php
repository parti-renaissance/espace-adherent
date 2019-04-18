<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestHandler;
use AppBundle\Donation\DonationRequestUtils;
use AppBundle\Donation\PayboxFormFactory;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Donation\PayboxPaymentUnsubscription;
use AppBundle\Donation\TransactionCallbackHandler;
use AppBundle\Entity\Donation;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Exception\InvalidPayboxPaymentSubscriptionValueException;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Form\ConfirmActionType;
use AppBundle\Form\DonationRequestType;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Membership\MembershipRegistrationProcess;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';
    public const RESULT_STATUS_ERREUR = 'erreur';

    /**
     * @Route(name="donation_index")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->render('donation/index.html.twig', [
                'amount' => DonationRequest::DEFAULT_AMOUNT,
                'abonnement' => $request->query->has('abonnement'),
            ]);
        }

        return $this->redirectToRoute('donation_informations', [
            'montant' => $amount,
            'abonnement' => $request->query->getInt('abonnement') ? // Force unlimited subscription if needed
                PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE,
        ]);
    }

    /**
     * @Route("/coordonnees", name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(
        Request $request,
        DonationRequestUtils $donationRequestUtils,
        DonationRequestHandler $donationRequestHandler
    ): Response {
        if (!$request->query->get('montant') || !is_numeric($request->query->get('montant'))) {
            return $this->redirectToRoute('donation_index');
        }

        try {
            $form = $this->createForm(DonationRequestType::class, null, ['locale' => $request->getLocale()]);

            /** @var DonationRequest $donationRequest */
            $donationRequest = $form->handleRequest($request)->getData();
        } catch (InvalidPayboxPaymentSubscriptionValueException $e) {
            return $this->redirectToRoute('donation_index');
        }

        if ($form->isSubmitted()) {
            $donationRequestUtils->startDonationRequest($donationRequest);

            if ($form->isValid()) {
                $donationRequestHandler->handle($donationRequest);
                $donationRequestUtils->terminateDonationRequest();

                return $this->redirectToRoute('donation_pay', [
                    'uuid' => $donationRequest->getUuid()->toString(),
                ]);
            }
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => $donationRequest,
        ]);
    }

    /**
     * @Route("/{uuid}/paiement", requirements={"uuid": "%pattern_uuid%"}, name="donation_pay")
     * @Method("GET")
     */
    public function payboxAction(PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation);

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback/{_callback_token}", name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(
        Request $request,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token
    ): Response {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $transactionCallbackHandler->handle($id, $request, $_callback_token);
    }

    /**
     * @Route(
     *     "/{uuid}/{status}",
     *     requirements={"status": "effectue|erreur", "uuid": "%pattern_uuid%"},
     *     name="donation_result"
     * )
     * @ParamConverter("donation", options={"mapping": {"uuid": "uuid"}})
     * @Method("GET")
     */
    public function resultAction(
        Request $request,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        AdherentRepository $adherentRepository,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        Donation $donation,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = self::RESULT_STATUS_EFFECTUE === $status;

        if (!$successful) {
            $retryUrl = $this->generateUrl(
                'donation_informations',
                $this->get(DonationRequestUtils::class)->createRetryPayload($donation, $request)
            );
        }

        $membershipRegistrationProcess->terminate();

        return $this->render('donation/result.html.twig', [
            'successful' => $successful,
            'nb_adherent' => $adherentRepository->countAdherents(),
            'result_code' => $request->query->get('result'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
            'is_registration' => $request->query->get('is_registration'),
            'is_adherent' => $adherentRepository->isAdherent($donation->getEmailAddress()),
            'is_newsletter_subscribed' => $newsletterSubscriptionRepository->isSubscribed($donation->getEmailAddress()),
            'newsletter_form' => $this
                ->createForm(
                    NewsletterSubscriptionType::class,
                    new NewsletterSubscription($donation->getEmailAddress(), $donation->getPostalCode(), $donation->getCountry())
                )
                ->createView(),
        ]);
    }

    /**
     * @Route("/mensuel/annuler", name="donation_subscription_cancel")
     * @Method({"GET", "POST"})
     */
    public function cancelSubscriptionAction(
        Request $request,
        DonationRepository $donationRepository,
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription,
        LoggerInterface $logger
    ): Response {
        $donations = $donationRepository->findAllSubscribedDonationByEmail($this->getUser()->getEmailAddress());

        if (!$donations) {
            $this->addFlash(
                'danger',
                'Aucun don mensuel n\'a été trouvé'
            );

            return $this->redirectToRoute('app_user_profile_donation');
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

            return $this->redirectToRoute('app_user_profile_donation');
        }

        return $this->render('user/donation_subscription_cancel_confirmation.html.twig', [
            'confirmation_form' => $form->createView(),
        ]);
    }
}
