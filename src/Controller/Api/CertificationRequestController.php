<?php

namespace App\Controller\Api;

use App\Adherent\Certification\CertificationManager;
use App\Adherent\Certification\CertificationPermissions;
use App\Api\DTO\ImageContent;
use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
#[Route(path: '/v3/profile/me/certification-request', name: 'app_api_user_profile_certification_request')]
class CertificationRequestController extends AbstractController
{
    #[Route(name: '_get', methods: ['GET'])]
    public function show(SerializerInterface $serializer): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return JsonResponse::fromJsonString(
            $serializer->serialize($user, 'json', [
                AbstractObjectNormalizer::GROUPS => ['certification_request_read'],
            ])
        );
    }

    #[IsGranted(CertificationPermissions::REQUEST)]
    #[Route(name: '_post', methods: ['POST'])]
    public function post(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CertificationManager $certificationManager,
    ): JsonResponse {
        /** @var Adherent $user */
        $user = $this->getUser();

        $certificationRequest = $certificationManager->createRequest($user);

        /** @var ImageContent $image */
        $image = $serializer->deserialize($request->getContent(), ImageContent::class, JsonEncoder::FORMAT);

        $certificationRequest->setDocument($image->getFile());

        $errors = $validator->validate($certificationRequest);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $certificationManager->handleRequest($certificationRequest);

        return JsonResponse::fromJsonString(
            $serializer->serialize($certificationRequest, 'json', [
                AbstractObjectNormalizer::GROUPS => ['certification_request_read'],
            ])
        );
    }
}
