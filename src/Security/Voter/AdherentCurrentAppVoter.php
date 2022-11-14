<?php

namespace App\Security\Voter;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\HttpFoundation\RequestStack;

class AdherentCurrentAppVoter extends AbstractAdherentVoter
{
    public const PERMISSION_CURRENT_APP = 'ADHERENT_CURRENT_APP';

    private RequestStack $requestStack;
    private AuthAppUrlManager $authAppUrlManager;

    public function __construct(RequestStack $requestStack, AuthAppUrlManager $authAppUrlManager)
    {
        $this->requestStack = $requestStack;
        $this->authAppUrlManager = $authAppUrlManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return self::PERMISSION_CURRENT_APP === $attribute && null === $subject;
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
