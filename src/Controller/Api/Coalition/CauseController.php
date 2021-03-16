<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Cause;
use App\Repository\Coalition\CauseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class CauseController
{
    public function followed(Request $request, UserInterface $user, CauseRepository $causeRepository): JsonResponse
    {
        /** @var string[]|array $uuids */
        $uuids = $request->query->get('uuids');

        if (!\is_array($uuids) || empty($uuids)) {
            throw new BadRequestHttpException('Parameter "uuids" should be an array of uuids.');
        }

        $causes = $causeRepository->findFollowedByUuids($uuids, $user);

        return JsonResponse::create(array_map(function (Cause $cause) {
            return $cause->getUuid();
        }, $causes));
    }
}
