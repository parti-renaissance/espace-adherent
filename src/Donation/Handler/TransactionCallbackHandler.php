<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Donation\Request\DonationRequestUtils;
use App\Repository\DonationRepository;
use App\Utils\UtmParams;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransactionCallbackHandler
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly DonationRepository $donationRepository,
        private readonly DonationRequestUtils $donationRequestUtils,
    ) {
    }

    public function handle(
        string $donationUuid,
        Request $request,
        string $callbackToken,
    ): Response {
        $donation = $this->donationRepository->findOneByUuid($donationUuid);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('app_donation_index'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);

        return new RedirectResponse($this->router->generate(
            'app_payment_status',
            UtmParams::mergeParams(
                $this->donationRequestUtils->createCallbackStatus($payload['result'], $donationUuid),
                $donation->utmSource,
                $donation->utmCampaign
            )
        ));
    }
}
