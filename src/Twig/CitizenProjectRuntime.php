<?php

namespace AppBundle\Twig;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CitizenProjectRuntime
{
    private $authorizationChecker;
    private $citizenProjectManager;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CitizenProjectManager $citizenProjectManager = null
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->citizenProjectManager = $citizenProjectManager;
    }

    public function isPromotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$this->citizenProjectManager) {
            return false;
        }

        return $this->citizenProjectManager->isPromotableAdministrator($adherent, $citizenProject);
    }

    public function isDemotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$this->citizenProjectManager) {
            return false;
        }

        return $this->citizenProjectManager->isDemotableAdministrator($adherent, $citizenProject);
    }

    public function isAdministrator(CitizenProject $citizenProject): bool
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
}
