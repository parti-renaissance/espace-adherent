<?php

namespace App\Twig;

use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Repository\CommitteeCandidacyRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CommitteeRuntime implements RuntimeExtensionInterface
{
    private $authorizationChecker;
    private $committeeManager;
    private $committeeCandidacyRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CommitteeCandidacyRepository $committeeCandidacyRepository,
        ?CommitteeManager $committeeManager = null,
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
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::HOST, $committee);
    }

    public function isSupervisor(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::SUPERVISE, $committee);
    }

    public function canFollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::FOLLOW, $committee);
    }

    public function canUnfollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::UNFOLLOW, $committee);
    }

    public function canCreate(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::CREATE, $committee);
    }

    public function canSee(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::SHOW, $committee);
    }

    public function isCandidate(Adherent $adherent, Committee $committee): bool
    {
        $membership = $this->committeeManager->getCommitteeMembership($adherent, $committee);

        return $membership && $membership->isVotingCommittee() && $membership->getCommitteeCandidacy($committee->getCommitteeElection());
    }

    public function countCommitteeCandidates(Committee $committee, ?bool $countMaleOnly = null): int
    {
        $candidacies = $this->committeeCandidacyRepository->findByCommittee($committee, $committee->getCurrentDesignation());

        if (null === $countMaleOnly) {
            return \count($candidacies);
        }

        return \count(array_filter($candidacies, static function (CommitteeCandidacy $candidacy) use ($countMaleOnly) {
            return $countMaleOnly ? !$candidacy->isFemale() : $candidacy->isFemale();
        }));
    }
}
