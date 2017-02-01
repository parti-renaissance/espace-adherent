<?php

namespace AppBundle\Twig;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommitteeExtension extends \Twig_Extension
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_host', [$this, 'isHost']),
            new \Twig_SimpleFunction('can_follow', [$this, 'canFollow']),
            new \Twig_SimpleFunction('can_unfollow', [$this, 'canUnfollow']),
            new \Twig_SimpleFunction('can_create', [$this, 'canCreate']),
            new \Twig_SimpleFunction('can_see', [$this, 'canSee']),
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
}
