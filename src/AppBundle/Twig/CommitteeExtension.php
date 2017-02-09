<?php

namespace AppBundle\Twig;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\CommitteeUrlGenerator;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommitteeExtension extends \Twig_Extension
{
    private $authorizationChecker;
    private $urlGenerator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CommitteeUrlGenerator $urlGenerator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            // Permissions
            new \Twig_SimpleFunction('is_host', [$this, 'isHost']),
            new \Twig_SimpleFunction('can_follow', [$this, 'canFollow']),
            new \Twig_SimpleFunction('can_unfollow', [$this, 'canUnfollow']),
            new \Twig_SimpleFunction('can_create', [$this, 'canCreate']),
            new \Twig_SimpleFunction('can_see', [$this, 'canSee']),

            // Routing
            new \Twig_SimpleFunction('committee_path', [$this, 'getPath']),
            new \Twig_SimpleFunction('committee_url', [$this, 'getUrl']),
        ];
    }

    public function isHost(Committee $committee): bool
    {
        return $this->authorizationChecker->isGranted(CommitteePermissions::HOST, $committee);
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
