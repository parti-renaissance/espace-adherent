<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\DTO\ImageContent;
use App\Image\ImageUploadHelper;
use App\Normalizer\ImageExposeNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateImageController extends AbstractController
{
    public function __construct(
        private readonly ImageUploadHelper $imageUploadHelper,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $object = $request->attributes->get('data');
        if ($request->isMethod(Request::METHOD_DELETE)) {
            $this->imageUploadHelper->removeImage($object);

            return $this->json('OK');
        }

        $data = $request->toArray();

        $image = new ImageContent();
        $image->setContent($data['content'] ?? null);

        $errors = $this->validator->validate($image);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->imageUploadHelper->uploadImage($object, $image);

        return $this->json($object, Response::HTTP_OK, [], ['groups' => [ImageExposeNormalizer::NORMALIZATION_GROUP]]);
    }
}
