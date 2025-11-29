<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissionEnum;
use App\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PromoteToHostInCommitteeVoter extends Voter
{
    /** @var CommitteeManager */
    private $committeeManager;

    public function __construct(CommitteeManager $committeeManager)
    {
        $this->committeeManager = $committeeManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return CommitteePermissionEnum::PROMOTE_TO_HOST === $attribute && $subject instanceof Committee;
    }

    /**
     * @param Committee $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $subject->isApproved() && $this->committeeManager->countCommitteeHosts($subject, true) < 2;
    }
}
