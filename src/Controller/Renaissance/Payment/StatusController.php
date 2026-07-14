<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Transaction;
use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route(path: '/paiement', name: 'app_payment_status', methods: ['GET'])]
class StatusController extends AbstractController
{
    public const SESSION_KEY = 'donation_uuid';

    private PostHogService $postHog;
    private DonationRepository $donationRepository;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    #[Required]
    public function setDonationRepository(DonationRepository $donationRepository): void
    {
        $this->donationRepository = $donationRepository;
    }

    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils): Response
    {
        $resultCode = $request->query->get('result');

        if ($uuid = $request->query->get('uuid')) {
            if (Transaction::PAYBOX_SUCCESS === $resultCode) {
                $request->getSession()->set(self::SESSION_KEY, $uuid);
            } else {
                $retryUrl = $this->generateUrl('app_payment_retry', [
                    'uuid' => $uuid,
                    '_retry_token' => $donationRequestUtils->generateRetryToken(),
                ]);
            }

            // PostHog capture: distinguish adhesion vs donation via $donation->isMembership().
            // Cas 1 forcé — jamais de $set.email.
            $donation = $this->donationRepository->findOneByUuid($uuid);
            if ($donation) {
                $isMembership = $donation->isMembership();
                $user = $donation->getDonator()?->getAdherent();

                if ($isMembership) {
                    if (Transaction::PAYBOX_SUCCESS === $resultCode) {
                        $this->postHog->captureServerSide(
                            PostHogEventName::ADHESION_COMPLETED,
                            [
                                'amount_eur' => $donation->getAmountInEuros(),
                                'payment_method' => 'card',
                                'is_first_adhesion' => !$donation->isReAdhesion(),
                            ],
                            $user,
                        );
                    } else {
                        $this->postHog->captureServerSide(
                            PostHogEventName::ADHESION_PAYMENT_FAILED,
                            ['reason' => $resultCode ?: 'unknown'],
                            $user,
                        );
                    }
                }
            }
        }

        return $this->render('renaissance/payment/status.html.twig', [
            'result_code' => $resultCode,
            'uuid' => $uuid,
            'retry_url' => $retryUrl ?? null,
        ]);
    }
}
