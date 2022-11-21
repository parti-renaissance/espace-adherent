<?php

namespace App\Controller\Api;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\Adherent;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FollowController extends AbstractController
{
    public function follower(Request $request, FollowedInterface $data): ?FollowerInterface
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        if (!$user instanceof Adherent) {
            throw new AccessDeniedHttpException('No adherent to add as follower');
        }

        if ($request->isMethod(Request::METHOD_DELETE)) {
            return $data->getFollower($user);
        }

        return $data->createFollower($user);
    }
}
