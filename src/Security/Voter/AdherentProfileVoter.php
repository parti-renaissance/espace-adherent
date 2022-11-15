<?php

namespace App\Security\Voter;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\HttpFoundation\RequestStack;

class AdherentProfileVoter extends AbstractAdherentVoter
{
    public const PERMISSION_ADHERENT_PROFILE = 'ADHERENT_PROFILE';

    private RequestStack $requestStack;
    private AuthAppUrlManager $authAppUrlManager;

    public function __construct(RequestStack $requestStack, AuthAppUrlManager $authAppUrlManager)
    {
        $this->requestStack = $requestStack;
        $this->authAppUrlManager = $authAppUrlManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return self::PERMISSION_ADHERENT_PROFILE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $appCode = $this->authAppUrlManager->getAppCodeFromRequest($this->requestStack->getCurrentRequest());

        return AppCodeEnum::isRenaissanceApp($appCode)
            ? $adherent->isRenaissanceUser()
            : !$adherent->isRenaissanceUser()
        ;
    }
}
