<?php

namespace App\Security\Voter;

use App\Membership\MembershipRegistrationPermissions;
use App\Membership\MembershipRegistrationProcess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MembershipRegistrationVoter extends Voter
{
    private $membershipRegistrationProcess;

    public function __construct(MembershipRegistrationProcess $membershipRegistrationProcess)
    {
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
    }

    protected function supports($attribute, $subject)
    {
        return MembershipRegistrationPermissions::REGISTRATION_IN_PROGRESS === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->membershipRegistrationProcess->isStarted();
    }
}
