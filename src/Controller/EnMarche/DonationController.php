<?php

namespace App\Controller\EnMarche;

use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Handler\TransactionCallbackHandler;
use App\Donation\Paybox\PayboxFormFactory;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Donation\Request\DonationRequest;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use App\Entity\NewsletterSubscription;
use App\Exception\InvalidPayboxPaymentSubscriptionValueException;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Form\ConfirmActionType;
use App\Form\DonationRequestType;
use App\Form\NewsletterSubscriptionType;
use App\Membership\MembershipRegistrationProcess;
use App\Repository\AdherentRepository;
use App\Repository\DonationRepository;
use App\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/don')]
class DonationController extends AbstractController
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';
    public const RESULT_STATUS_ERREUR = 'erreur';

    #[Route(name: 'donation_index', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->render('donation/index.html.twig', [
                'abonnement' => $request->query->has('abonnement'),
            ]);
        }

        return $this->redirectToRoute('donation_informations', [
            'montant' => $amount,
            'abonnement' => $request->query->getInt('abonnement') ? // Force unlimited subscription if needed
                PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE,
        ]);
    }

    #[Route(path: '/coordonnees', name: 'donation_informations', methods: ['GET', 'POST'])]
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
                $donation = $donationRequestHandler->handle($donationRequest);
                $donationRequestUtils->terminateDonationRequest();

                return $this->redirectToRoute('donation_pay', [
                    'uuid' => $donation->getUuid(),
                ]);
            }
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => $donationRequest,
        ]);
    }

    #[Route(path: '/{uuid}/paiement', requirements: ['uuid' => '%pattern_uuid%'], name: 'donation_pay', methods: ['GET'])]
    public function payboxAction(PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation, 'app_renaissance_donation_callback');

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    #[Route(path: '/callback/{_callback_token}', name: 'donation_callback', methods: ['GET'])]
    public function callbackAction(
        Request $request,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token
    ): Response {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $transactionCallbackHandler->handle($id, $request, $_callback_token, 'app_renaissance_donation_payment_result');
    }

    #[Route(path: '/{uuid}/{status}', requirements: ['status' => 'effectue|erreur', 'uuid' => '%pattern_uuid%'], name: 'donation_result', methods: ['GET'])]
    #[ParamConverter('donation', options: ['mapping' => ['uuid' => 'uuid']])]
    public function resultAction(
        Request $request,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        AdherentRepository $adherentRepository,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        Donation $donation,
        DonationRequestUtils $donationRequestUtils,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = self::RESULT_STATUS_EFFECTUE === $status;

        if (!$successful) {
            $retryUrl = $this->generateUrl(
                'donation_informations',
                $donationRequestUtils->createRetryPayload($donation, $request)
            );
        }

        $membershipRegistrationProcess->terminate();

        $emailAddress = $donation->getDonator()->getEmailAddress();

        return $this->render('donation/result.html.twig', [
            'successful' => $successful,
            'nb_adherent' => $adherentRepository->countAdherents(),
            'result_code' => $request->query->get('result'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
            'is_registration' => $request->query->get('is_registration'),
            'is_adherent' => $adherentRepository->isAdherent($emailAddress),
            'is_newsletter_subscribed' => $newsletterSubscriptionRepository->isSubscribed($emailAddress),
            'newsletter_form' => $this
                ->createForm(
                    NewsletterSubscriptionType::class,
                    new NewsletterSubscription($emailAddress, $donation->getPostalCode(), $donation->getCountry())
                )
                ->createView(),
        ]);
    }

    #[Route(path: '/mensuel/annuler', name: 'donation_subscription_cancel', methods: ['GET', 'POST'])]
    public function cancelSubscriptionAction(
        EntityManagerInterface $manager,
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

            return $this->redirectToRoute('app_adherent_profile_activity', ['_fragment' => 'donations']);
        }

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('allow')->isClicked()) {
                foreach ($donations as $donation) {
                    try {
                        $payboxPaymentUnsubscription->unsubscribe($donation);
                        $manager->flush();
                        $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $this->getUser());
                        $this->addFlash(
                            'success',
                            'Votre don mensuel a bien été annulé. Vous recevrez bientôt un email de confirmation.'
                        );
                        $logger->info(sprintf('Subscription donation id(%d) from user email %s have been cancel successfully.', $donation->getId(), $this->getUser()->getEmailAddress()));
                    } catch (PayboxPaymentUnsubscriptionException $e) {
                        $this->addFlash('danger', 'La requête n\'a pas abouti, veuillez réessayer s\'il vous plait. Si le problème persiste, merci de nous contacter en <a href="https://contact.en-marche.fr/" target="_blank">cliquant ici</a>');

                        $logger->error(sprintf('Subscription donation id(%d) from user email %s have an error.', $donation->getId(), $this->getUser()->getEmailAddress()), ['exception' => $e]);
                    }
                }
            }

            return $this->redirectToRoute('app_adherent_profile_activity', ['_fragment' => 'donations']);
        }

        return $this->render('user/donation_subscription_cancel_confirmation.html.twig', [
            'confirmation_form' => $form->createView(),
        ]);
    }
}
