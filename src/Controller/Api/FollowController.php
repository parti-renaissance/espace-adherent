<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class FollowController
{
    public function follower(Request $request, UserInterface $user, FollowedInterface $data): ?FollowerInterface
    {
        if (!$user instanceof Adherent) {
            throw new AccessDeniedHttpException('No adherent to add as follower');
        }

        if ($request->isMethod(Request::METHOD_DELETE)) {
            return $data->getFollower($user);
        }

        return $data->createFollower($user);
    }
}
