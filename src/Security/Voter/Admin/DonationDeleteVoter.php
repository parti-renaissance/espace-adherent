<?php

namespace AppBundle\Security\Voter\Admin;

use AppBundle\Entity\Administrator;
use AppBundle\Entity\Donation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DonationDeleteVoter extends Voter
{
    private const ROLE = 'ROLE_APP_ADMIN_DONATION_DELETE';

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function supports($attribute, $subject)
    {
        return self::ROLE === $attribute && $subject instanceof Donation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof Administrator) {
            return false;
        }

        if (!$this->authorizationChecker->isGranted(['ROLE_ADMIN_FINANCE'])) {
            return false;
        }

        return Donation::TYPE_CB !== $subject->getType();
    }
}
