<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Cause;
use App\Repository\Coalition\CauseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CauseController
{
    /**
     * @Route("/v3/causes/followed", name="api_causes_followed", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function followed(Request $request, UserInterface $user, CauseRepository $causeRepository): JsonResponse
    {
        /** @var string[]|array $uuids */
        $uuids = $request->request->get('uuids');

        if (!\is_array($uuids) || empty($uuids)) {
            throw new BadRequestHttpException('Parameter "uuids" should be an array of uuids.');
        }

        $causes = $causeRepository->findFollowedByUuids($uuids, $user);

        return JsonResponse::create(array_map(function (Cause $cause) {
            return $cause->getUuid();
        }, $causes));
    }
}
