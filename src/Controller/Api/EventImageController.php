<?php

namespace App\Controller\Api;

use App\Api\DTO\ImageContent;
use App\Entity\Event\BaseEvent;
use App\Image\ImageManagerInterface;
use App\Image\ImageUploadHelper;
use App\Normalizer\ImageOwnerExposedNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Security("is_granted('ROLE_ADHERENT') and is_granted('CAN_MANAGE_EVENT', event)")
 */
class EventImageController extends AbstractController
{
    private ImageUploadHelper $imageUploadHelper;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private ImageManagerInterface $imageManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ImageUploadHelper $imageUploadHelper,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ImageManagerInterface $imageManager,
        EntityManagerInterface $entityManager
    ) {
        $this->imageUploadHelper = $imageUploadHelper;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->imageManager = $imageManager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request, BaseEvent $event): JsonResponse
    {
        if ($request->isMethod(Request::METHOD_DELETE)) {
            if ($event->hasImageName()) {
                $this->imageManager->removeImage($event);
                $this->entityManager->flush();
            }

            return $this->json('OK');
        }

        /** @var ImageContent $image */
        $image = $this->serializer->deserialize($request->getContent(), ImageContent::class, JsonEncoder::FORMAT);

        $errors = $this->validator->validate($image);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->imageUploadHelper->uploadImage($event, $image);

        return $this->json($event, Response::HTTP_OK, [], ['groups' => ['event_read', ImageOwnerExposedNormalizer::NORMALIZATION_GROUP]]);
    }
}
