<?php

namespace App\Adherent\SessionModal;

use App\Entity\Adherent;
use App\Entity\CommitteeMembership;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SessionModalActivatorListener implements EventSubscriberInterface
{
    public const DISMISS_COOKIE_KEY = 'session_modal_dismiss';
    public const SESSION_KEY = 'session_modal';

    public const CONTEXT_CERTIFICATION = 'certification';
    public const CONTEXT_COMMITTEE_ELECTION = 'committee_election';
    public const COMMITTEE_MEMBERSHIP_LIST = 'committee_membership_list';

    private $electedRepresentativeRepository;

    public function __construct(ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();

        if ($token instanceof PostAuthenticationGuardToken && 'api_oauth' === $token->getProviderKey()) {
            return;
        }

        $adherent = $token->getUser();
        // Only record adherent logins
        if (!$adherent instanceof Adherent) {
            return;
        }

        $request = $event->getRequest();

        if ($request->cookies->has(self::DISMISS_COOKIE_KEY)) {
            $request->getSession()->remove(self::SESSION_KEY);

            return;
        }

        $refDate = new \DateTimeImmutable();

        if ($adherent->getRegisteredAt() > $refDate->modify('-3 months')) {
            return;
        }

        if (!$memberships = $adherent->getMemberships()->getMembershipsForApprovedCommittees()) {
            return;
        }

        $availableCommitteeMemberships = array_filter($memberships, function (CommitteeMembership $membership) use ($refDate) {
            return $membership->getSubscriptionDate() <= $refDate->modify('-30 days')
                && $membership->getCommittee()->hasActiveElection()
                && DesignationTypeEnum::COMMITTEE_SUPERVISOR === $membership->getCommittee()->getCurrentElection()->getDesignationType();
        });

        if (!$availableCommitteeMemberships) {
            return;
        }

        if (!$adherent->isCertified()) {
            $request->getSession()->set(self::SESSION_KEY, self::CONTEXT_CERTIFICATION);

            return;
        }

        if ($adherent->isMinor()) {
            return;
        }

        if ($this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent)) {
            return;
        }

        $request->getSession()->set(self::SESSION_KEY, self::CONTEXT_COMMITTEE_ELECTION);
        $request->getSession()->set(self::COMMITTEE_MEMBERSHIP_LIST, array_map(function (CommitteeMembership $membership) {
            return $membership->getId();
        }, $availableCommitteeMemberships));
    }
}
