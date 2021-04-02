<?php

namespace App\Controller\Api\Coalition;

use App\Api\DTO\ImageContent;
use App\Entity\Coalition\Cause;
use App\Image\ImageManager;
use App\Normalizer\ImageOwnerExposedNormalizer;
use App\Repository\Coalition\CauseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CauseController extends AbstractController
{
    /**
     * @Route("/v3/causes/followed", name="api_causes_followed", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function followed(Request $request, UserInterface $user, CauseRepository $causeRepository): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $uuids = $body['uuids'] ?? null;

        if (!\is_array($uuids) || empty($uuids)) {
            throw new BadRequestHttpException('Parameter "uuids" should be an array of uuids.');
        }

        $causes = $causeRepository->findFollowedByUuids($uuids, $user);

        return JsonResponse::create(array_map(function (Cause $cause) {
            return $cause->getUuid();
        }, $causes));
    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY') and cause.getAuthor() === user")")
     */
    public function updateImage(
        Request $request,
        Cause $cause,
        EntityManagerInterface $entityManager,
        ImageManager $imageManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var ImageContent $image */
        $image = $serializer->deserialize($request->getContent(), ImageContent::class, JsonEncoder::FORMAT);

        $errors = $validator->validate($image);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ($cause->hasImageName()) {
            $imageManager->removeImage($cause);
        }

        $cause->setImage($image->getFile());
        $imageManager->saveImage($cause);

        $entityManager->flush();

        return $this->json($cause, Response::HTTP_OK, [], ['groups' => ['cause_read', ImageOwnerExposedNormalizer::NORMALIZATION_GROUP]]);
    }
}
