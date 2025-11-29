<?php

declare(strict_types=1);

namespace App\Controller\Api\Petition;

use App\Renaissance\Petition\SignatureManager;
use App\Renaissance\Petition\SignatureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/petition/signature', methods: ['POST'])]
class SaveNewSignatureController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly SignatureManager $signatureManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $signature = $this->serializer->deserialize($request->getContent(), SignatureRequest::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::GROUPS => ['petition:write'],
        ]);

        $errors = $this->validator->validate($signature);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->signatureManager->save($signature);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
