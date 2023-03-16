<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationRequestUtils;
use App\Donation\PayboxFormFactory;
use App\Donation\TransactionCallbackHandler;
use App\Entity\Donation;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation);

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

        return $transactionCallbackHandler->handle($id, $request, $_callback_token);
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
}
