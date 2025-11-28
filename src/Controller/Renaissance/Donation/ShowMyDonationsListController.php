<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationManager;
use App\Entity\Adherent;
use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-adherent/mes-dons', name: 'app_my_donations_show_list', methods: ['GET'])]
class ShowMyDonationsListController extends AbstractController
{
    public function __invoke(DonationManager $donationManager, DonationRepository $donationRepository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render('renaissance/adherent/my_donations/list.html.twig', [
            'donations_history' => $donationManager->getHistory($adherent),
            'subscribed_donations' => $donationRepository->findAllSubscribedDonationByEmail($adherent->getEmailAddress()),
            'last_subscription_ended' => $donationRepository->findLastSubscriptionEndedDonationByEmail($adherent->getEmailAddress()),
        ]);
    }
}
