<?php

namespace AppBundle\Report\Voter;

use AppBundle\Entity\Administrator;
use AppBundle\Report\ReportPermissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReportVoter extends Voter
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return ReportPermissions::REPORT === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($token->getUser() instanceof Administrator) {
            return false;
        }

        return $this->authorizationChecker->isGranted(['IS_AUTHENTICATED_FULLY']);
    }
}
