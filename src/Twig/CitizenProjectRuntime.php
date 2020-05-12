<?php

namespace App\Twig;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CitizenProjectRuntime implements RuntimeExtensionInterface
{
    private const COLOR_STATUS_NOT_FINAL = 'text--gray';
    private const COLOR_STATUS_ADMINISTRATOR = 'text--bold text--blue--dark';

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isAdministratorOf(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::ADMINISTRATE, $citizenProject);
    }

    public function canFollowCitizenProject(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::FOLLOW, $citizenProject);
    }

    public function canUnfollowCitizenProject(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::UNFOLLOW, $citizenProject);
    }

    public function canSeeCitizenProject(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::SHOW, $citizenProject);
    }

    public function canCommentCitizenProject(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::COMMENT, $citizenProject);
    }

    public function canSeeCommentCitizenProject(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::SHOW_COMMENT, $citizenProject);
    }

    public function getCitizenProjectColorStatus(Adherent $adherent, CitizenProject $citizenProject): string
    {
        if ($citizenProject->isNotFinalStatus()) {
            return self::COLOR_STATUS_NOT_FINAL;
        }

        if ($adherent->isAdministratorOf($citizenProject)) {
            return self::COLOR_STATUS_ADMINISTRATOR;
        }

        return '';
    }
}
