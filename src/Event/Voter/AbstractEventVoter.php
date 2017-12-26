<?php

namespace AppBundle\Event\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractEventVoter extends Voter
{
    /**
     * Votes on an attribute.
     *
     * @param string         $attribute
     * @param Event          $event
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $event, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $adherent, $event);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Event $event): bool;
}
