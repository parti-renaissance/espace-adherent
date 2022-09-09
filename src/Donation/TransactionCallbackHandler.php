<?php

namespace App\Donation;

use App\Repository\DonationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransactionCallbackHandler
{
    private $router;
    private $donationRepository;
    private $donationRequestUtils;

    public function __construct(
        UrlGeneratorInterface $router,
        DonationRepository $donationRepository,
        DonationRequestUtils $donationRequestUtils
    ) {
        $this->router = $router;
        $this->donationRepository = $donationRepository;
        $this->donationRequestUtils = $donationRequestUtils;
    }

    public function handle(
        string $donationUuid,
        Request $request,
        string $callbackToken,
        bool $isRenaissanceDonationRequest = false
    ): Response {
        $donation = $this->donationRepository->findOneByUuid($donationUuid);

        if (!$donation) {
            if ($donation->isForMembership()) {
                return new RedirectResponse($this->router->generate('app_renaissance_adhesion'));
            }

            return new RedirectResponse($this->router->generate($isRenaissanceDonationRequest ? 'app_renaissance_donation' : 'donation_index'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);

        $ResultRouteName = $isRenaissanceDonationRequest ? 'app_renaissance_donation_payment_result' : 'donation_result';

        return new RedirectResponse($this->router->generate(
            $donation->isForMembership() ? 'app_renaissance_adhesion_payment_result' : $ResultRouteName,
            $this->donationRequestUtils->createCallbackStatus($payload['result'], $donationUuid)
        ));
    }
}
