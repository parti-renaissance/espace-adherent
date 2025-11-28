<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Donation\Handler\TransactionCallbackHandler;
use App\Repository\DonationRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/paiement/callback/{_callback_token}', name: 'app_payment_callback', requirements: ['_callback_token' => '.+'], methods: ['GET'])]
class CallbackController extends AbstractController
{
    public function __invoke(
        Request $request,
        DonationRepository $donationRepository,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token,
    ): Response {
        $donationUuid = explode('_', $request->query->get('id'))[0];

        if (
            !$donationUuid
            || !Uuid::isValid($donationUuid)
            || !$donationRepository->findOneByUuid($donationUuid)
        ) {
            return $this->redirectToRoute('app_payment_status', ['code' => 'error']);
        }

        return $transactionCallbackHandler->handle($donationUuid, $request, $_callback_token);
    }
}
