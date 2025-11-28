<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\AdherentProfile;

use App\Donation\DonationManager;
use App\Entity\Adherent;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\DonationRepository;
use App\Repository\EventRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/parametres/mes-activites', name: 'app_adherent_profile_activity', methods: ['GET'])]
class ActivityController extends AbstractController
{
    private const ITEMS_PER_PAGE = 5;

    public function __invoke(
        Request $request,
        CommitteeMembershipRepository $membershipRepository,
        DonationRepository $donationRepository,
        DonationManager $donationManager,
        EventRegistrationRepository $eventRegistrationRepository,
    ): Response {
        /** @var Adherent $user */
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $type = $request->query->get('type');

        return $this->render('adherent_profile/activity.html.twig', [
            'committee_memberships' => $membershipRepository->findActivityMemberships($user, 'committees' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'donations_history' => $donationManager->getHistory($user),
            'subscribed_donations' => $donationRepository->findAllSubscribedDonationByEmail($user->getEmailAddress()),
            'last_subscription_ended' => $donationRepository->findLastSubscriptionEndedDonationByEmail($user->getEmailAddress()),
            'past_events' => $eventRegistrationRepository->findActivityPastAdherentRegistrations($user, 'past_events' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'upcoming_events' => $eventRegistrationRepository->findActivityUpcomingAdherentRegistrations($user, 'upcoming_events' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'type' => $type,
        ]);
    }
}
