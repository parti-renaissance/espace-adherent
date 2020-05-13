<?php

namespace App\Twig;

use App\CitizenAction\CitizenActionPermissions;
use App\Entity\CitizenAction;
use App\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CitizenActionRuntime implements RuntimeExtensionInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function canCreateCitizenActionFor(CitizenProject $citizenProject): bool
    {
        return $this->authorizationChecker->isGranted(CitizenActionPermissions::CREATE, $citizenProject);
    }

    public function canRegisterOnCitizenAction(CitizenAction $citizenAction): bool
    {
        return $this->authorizationChecker->isGranted(CitizenActionPermissions::REGISTER, $citizenAction);
    }

    public function canUnregisterFromCitizenAction(CitizenAction $citizenAction): bool
    {
        return $this->authorizationChecker->isGranted(CitizenActionPermissions::UNREGISTER, $citizenAction);
    }
}
