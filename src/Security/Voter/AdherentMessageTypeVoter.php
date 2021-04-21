<?php

namespace App\Security\Voter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdherentMessageTypeVoter extends AbstractAdherentVoter
{
    public const USER_CAN_EDIT_MESSAGE_TYPE = 'USER_CAN_EDIT_MESSAGE_TYPE';

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$roles = AdherentMessageTypeEnum::ROLES[\get_class($subject)] ?? null) {
            return false;
        }

        foreach ((array) $roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::USER_CAN_EDIT_MESSAGE_TYPE === $attribute && $subject instanceof AdherentMessageInterface;
    }
}
