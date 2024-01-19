<?php

namespace App\Donation\Handler;

use App\Donation\Request\DonationRequestUtils;
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
        string $statusRouteName,
        bool $forMembership = false,
    ): Response {
        $donation = $this->donationRepository->findOneByUuid($donationUuid);

        if (!$donation) {
            if ($forMembership) {
                return new RedirectResponse($this->router->generate('app_adhesion_index'));
            }

            return new RedirectResponse($this->router->generate('app_donation_index'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);

        return new RedirectResponse($this->router->generate(
            $statusRouteName,
            $this->donationRequestUtils->createCallbackStatus($payload['result'], $donationUuid)
        ));
    }
}
