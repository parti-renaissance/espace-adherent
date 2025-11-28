<?php

declare(strict_types=1);

namespace App\Twig;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Repository\CommitteeCandidacyRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CommitteeRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly CommitteeCandidacyRepository $committeeCandidacyRepository,
    ) {
    }

    public function isHost(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::HOST, $committee);
    }

    public function canFollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::FOLLOW, $committee);
    }

    public function canUnfollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::UNFOLLOW, $committee);
    }

    public function canSee(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissionEnum::SHOW, $committee);
    }

    public function isCandidate(Adherent $adherent, Committee $committee): bool
    {
        $membership = $adherent->getMembershipFor($committee);

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
