<?php

namespace AppBundle\Twig;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\CommitteeUrlGenerator;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommitteeRuntime
{
    private $authorizationChecker;
    private $committeeManager;
    private $urlGenerator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CommitteeUrlGenerator $urlGenerator,
        CommitteeManager $committeeManager = null
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
        $this->committeeManager = $committeeManager;
    }

    public function isPromotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$this->committeeManager) {
            return false;
        }

        return $this->committeeManager->isPromotableHost($adherent, $committee);
    }

    public function isDemotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$this->committeeManager) {
            return false;
        }

        return $this->committeeManager->isDemotableHost($adherent, $committee);
    }

    public function isHost(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::HOST, $committee);
    }

    public function isSupervisor(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::SUPERVISE, $committee);
    }

    public function canFollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::FOLLOW, $committee);
    }

    public function canUnfollow(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::UNFOLLOW, $committee);
    }

    public function canCreate(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::CREATE, $committee);
    }

    public function canSee(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::SHOW, $committee);
    }

    public function getPath(string $routeName, Committee $committee, array $params = []): string
    {
        return $this->urlGenerator->getPath($routeName, $committee, $params);
    }

    public function getUrl(string $routeName, Committee $committee, array $params = []): string
    {
        return $this->urlGenerator->getUrl($routeName, $committee, $params);
    }
}
