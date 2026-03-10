<?php

declare(strict_types=1);

namespace App\Controller\Api\Adherent;

use App\Donation\DonationManager;
use App\Entity\Adherent;
use App\Security\Voter\ManagedUserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class GetDonationsController extends AbstractController
{
    public function __construct(private readonly DonationManager $donationManager)
    {
    }

    public function __invoke(Adherent $adherent): JsonResponse
    {
        $this->denyAccessUnlessGranted(ManagedUserVoter::IS_MANAGED_USER, $adherent);

        return $this->json(
            $this->donationManager->getHistory($adherent, false),
            context: [AbstractNormalizer::GROUPS => ['donation_read']]
        );
    }
}
