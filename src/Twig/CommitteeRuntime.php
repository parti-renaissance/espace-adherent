<?php

namespace AppBundle\Twig;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeCandidacy;
use AppBundle\Repository\CommitteeCandidacyRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommitteeRuntime
{
    private const COLOR_STATUS_NOT_FINAL = 'text--gray';
    private const COLOR_STATUS_ADMINISTRATOR = 'text--bold text--blue--dark';

    private $authorizationChecker;
    private $committeeManager;
    private $committeeCandidacyRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CommitteeCandidacyRepository $committeeCandidacyRepository,
        CommitteeManager $committeeManager = null
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->committeeCandidacyRepository = $committeeCandidacyRepository;
        $this->committeeManager = $committeeManager;
    }

    public function isPromotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$this->committeeManager) {
            return false;
        }

        return $this->committeeManager->isPromotableHost($adherent, $committee);
    }

    public function isDemotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$this->committeeManager) {
            return false;
        }

        return $this->committeeManager->isDemotableHost($adherent, $committee);
    }

    public function isHost(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::HOST, $committee);
    }

    public function isSupervisor(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::SUPERVISE, $committee);
    }

    public function canFollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::FOLLOW, $committee);
    }

    public function canUnfollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::UNFOLLOW, $committee);
    }

    public function canCreate(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::CREATE, $committee);
    }

    public function canSee(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::SHOW, $committee);
    }

    public function getCommitteeColorStatus(Adherent $adherent, Committee $committee): string
    {
        if ($adherent->isHostOf($committee)) {
            return self::COLOR_STATUS_ADMINISTRATOR;
        }

        if ($committee->isWaitingForApproval()) {
            return self::COLOR_STATUS_NOT_FINAL;
        }

        return '';
    }

    public function isCandidate(Adherent $adherent, Committee $committee): bool
    {
        $membership = $this->committeeManager->getCommitteeMembership($adherent, $committee);

        return $membership && $membership->isVotingCommittee() && $membership->getCommitteeCandidacy();
    }

    public function countCommitteeCandidates(Committee $committee, bool $countMaleOnly = null): int
    {
        $candidacies = $this->committeeCandidacyRepository->findByCommittee($committee);

        if (null === $countMaleOnly) {
            return \count($candidacies);
        }

        return \count(array_filter($candidacies, static function (CommitteeCandidacy $candidacy) use ($countMaleOnly) {
            return $countMaleOnly ? $candidacy->isMale() : $candidacy->isFemale();
        }));
    }
}
