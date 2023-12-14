<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\Handler\TransactionCallbackHandler;
use App\Donation\Paybox\PayboxFormFactory;
use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Form\ConfirmActionType;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractDonationController
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';
    public const RESULT_STATUS_ERREUR = 'erreur';

    #[Route(path: '/don/{uuid}/paiement', requirements: ['uuid' => '%pattern_uuid%'], name: 'app_renaissance_donation_payment', methods: ['GET'])]
    public function paymentAction(PayboxFormFactory $payboxFormFactory, Donation $donation)
    {
        $command = $this->getCommand();

        if (!$this->processor->canProceedDonationPayment($command)) {
            return $this->redirectToRoute('app_renaissance_donation');
        }

        $this->processor->doDonationPayment($command);

        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation, 'app_renaissance_donation_callback');

        return $this->render('renaissance/donation/payment.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    #[Route(path: '/don/callback/{_callback_token}', name: 'app_renaissance_donation_callback', methods: ['GET'])]
    public function callbackAction(
        Request $request,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token
    ): Response {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('app_renaissance_donation');
        }

        return $transactionCallbackHandler->handle($id, $request, $_callback_token, 'app_renaissance_donation_payment_result');
    }

    #[Route(path: '/don/{uuid}/{status}', requirements: ['status' => 'effectue|erreur', 'uuid' => '%pattern_uuid%'], name: 'app_renaissance_donation_payment_result', methods: ['GET'])]
    #[ParamConverter('donation', options: ['mapping' => ['uuid' => 'uuid']])]
    public function resultAction(
        Request $request,
        Donation $donation,
        DonationRequestUtils $donationRequestUtils,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = self::RESULT_STATUS_EFFECTUE === $status;
        $command = $this->getCommand();

        if (!$successful) {
            $retryUrl = $this->generateUrl(
                'app_renaissance_donation_informations',
                $donationRequestUtils->createRetryPayload($donation, $request)
            );
        }

        if ($this->processor->canFinishDonationRequest($command)) {
            $this->processor->doFinishDonationRequest($command);
        }

        return $this->render('renaissance/donation/result.html.twig', [
            'successful' => $successful,
            'result_code' => $request->query->get('result'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
        ]);
    }

    #[Route(path: '/don/mensuel/annuler', name: 'app_renaissance_donation__cancel_subscription', methods: ['GET', 'POST'])]
    public function cancelSubscriptionAction(
        EntityManagerInterface $manager,
        Request $request,
        DonationRepository $donationRepository,
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription,
        LoggerInterface $logger
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$adherent->isRenaissanceUser()) {
            return $this->redirectToRoute('renaissance_site');
        }

        $donations = $donationRepository->findAllSubscribedDonationByEmail($adherent->getEmailAddress());

        if (!$donations) {
            $this->addFlash('error', 'Aucun don mensuel n\'a été trouvé');

            return $this->redirect($this->generateUrl('app_my_donations_show_list', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('allow')->isClicked()) {
                foreach ($donations as $donation) {
                    try {
                        $payboxPaymentUnsubscription->unsubscribe($donation);
                        $manager->flush();
                        $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $adherent);
                        $this->addFlash(
                            'success',
                            'Votre don mensuel a bien été annulé. Vous recevrez bientôt un mail de confirmation.'
                        );
                        $logger->info(sprintf('Subscription donation id(%d) from user email %s have been cancel successfully.', $donation->getId(), $adherent->getEmailAddress()));
                    } catch (PayboxPaymentUnsubscriptionException $e) {
                        $this->addFlash('error', 'La requête n\'a pas abouti, veuillez réessayer s\'il vous plait. Si le problème persiste, merci de nous envoyer un mail à dons@parti-renaissance.fr');

                        $logger->error(sprintf('Subscription donation id(%d) from user email %s have an error.', $donation->getId(), $adherent->getEmailAddress()), ['exception' => $e]);
                    }
                }
            }

            return $this->redirect($this->generateUrl('app_my_donations_show_list', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->render('renaissance/adherent/my_donations/donation_subscription_cancel_confirmation.html.twig', [
            'confirmation_form' => $form->createView(),
        ]);
    }
}
