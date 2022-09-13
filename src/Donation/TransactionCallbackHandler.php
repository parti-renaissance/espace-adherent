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
        bool $forMembership = false
    ): Response {
        $donation = $this->donationRepository->findOneByUuid($donationUuid);

        if (!$donation) {
            if ($forMembership) {
                return new RedirectResponse($this->router->generate('app_renaissance_adhesion'));
            }

            return new RedirectResponse($this->router->generate('app_renaissance_donation'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);

        return new RedirectResponse($this->router->generate(
            $forMembership ? 'app_renaissance_adhesion_payment_result' : 'app_renaissance_donation_payment_result',
            $this->donationRequestUtils->createCallbackStatus($payload['result'], $donationUuid)
        ));
    }
}
