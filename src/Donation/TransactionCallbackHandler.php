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

    public function handle(string $donationUuid, Request $request, string $callbackToken): Response
    {
        $donation = $this->donationRepository->findOneByUuid($donationUuid);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);

        return new RedirectResponse($this->router->generate(
            'donation_result',
            $this->donationRequestUtils->createCallbackStatus($payload['result'], $donationUuid)
        ));
    }
}
