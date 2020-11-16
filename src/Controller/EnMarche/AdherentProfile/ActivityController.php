<?php

namespace App\Controller\EnMarche\AdherentProfile;

use App\Donation\DonationManager;
use App\Repository\CitizenProjectMembershipRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\DonationRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\IdeasWorkshop\IdeaRepository;
use App\Repository\VoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/parametres/mes-activites", name="app_adherent_profile_activity", methods={"GET"})
 */
class ActivityController extends AbstractController
{
    private const ITEMS_PER_PAGE = 5;

    public function __invoke(
        Request $request,
        UserInterface $user,
        CommitteeMembershipRepository $membershipRepository,
        CitizenProjectMembershipRepository $citizenProjectMembershipRepository,
        DonationRepository $donationRepository,
        DonationManager $donationManager,
        EventRegistrationRepository $eventRegistrationRepository,
        IdeaRepository $ideaRepository,
        VoteRepository $voteRepository
    ): Response {
        $page = $request->query->getInt('page', 1);
        $type = $request->query->get('type');

        return $this->render('adherent_profile/activity.html.twig', [
            'committee_memberships' => $membershipRepository->findActivityMemberships($user, 'committees' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'citizen_projects_created' => $citizenProjectMembershipRepository->findActivityCitizenProjectMemberships($user, 'citizen_projects_created' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'citizen_projects_joined' => $citizenProjectMembershipRepository->findActivityCitizenProjectMembershipsJoined($user, 'citizen_projects_joined' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'donations_history' => $donationManager->getHistory($user),
            'subscribed_donations' => $donationRepository->findAllSubscribedDonationByEmail($user->getEmailAddress()),
            'last_subscription_ended' => $donationRepository->findLastSubscriptionEndedDonationByEmail($user->getEmailAddress()),
            'past_events' => $eventRegistrationRepository->findActivityPastAdherentRegistrations($user, 'past_events' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'upcoming_events' => $eventRegistrationRepository->findActivityUpcomingAdherentRegistrations($user, 'upcoming_events' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'adi_proposals' => $ideaRepository->getIdeasProposalsFromAdherent($user, 'adi_proposals' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'adi_contributions' => $ideaRepository->getIdeaContributionsFromAdherent($user, 'adi_contributions' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'adi_votes' => $voteRepository->getIdeasVotesFromAdherent($user, 'adi_votes' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'type' => $type,
        ]);
    }
}
