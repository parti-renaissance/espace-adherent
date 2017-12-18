<?php

namespace AppBundle\Twig;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CitizenActionRuntime
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
}
