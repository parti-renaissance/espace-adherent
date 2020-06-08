<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DelegatedTypeVoter extends Voter
{
    private const IS_DELEGATED_SENATOR = 'IS_DELEGATED_SENATOR';
    private const IS_DELEGATED_DEPUTY = 'IS_DELEGATED_DEPUTY';
    private const IS_DELEGATED_REFERENT = 'IS_DELEGATED_REFERENT';

    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, [
            self::IS_DELEGATED_DEPUTY,
            self::IS_DELEGATED_REFERENT,
            self::IS_DELEGATED_SENATOR,
        ], true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return false;
        }

        $delegatedAccesses = $this->requestStack->getMasterRequest()->attributes->get('delegatedAccesses');

        if (!$delegatedAccesses) {
            return false;
        }

        switch ($attribute) {
            case self::IS_DELEGATED_SENATOR:
                $type = 'senator';
                break;
            case self::IS_DELEGATED_DEPUTY:
                $type = 'deputy';
                break;
            case self::IS_DELEGATED_REFERENT:
                $type = 'referent';
                break;
            default:
                throw new \LogicException('Unable to determine type');
        }

        foreach ($delegatedAccesses as $delegatedAccess) {
            if ($delegatedAccess->getType() === $type) {
                return true;
            }
        }

        return false;
    }
}
