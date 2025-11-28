<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion\Api;

use App\Adhesion\Request\EmailValidationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidateEmailController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $emailValidationRequest = $this->serializer->deserialize($request->getContent(), EmailValidationRequest::class, JsonEncoder::FORMAT, [
            'groups' => ['adhesion-email:validate'],
        ]);

        if (!$this->isCsrfTokenValid('email_validation_token', $emailValidationRequest->token)) {
            return $this->json(['message' => 'Token invalid', 'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($emailValidationRequest);

        if ($errors->count()) {
            $error = $errors[0]->getMessage();
            $errorLevel = $errors[0]->getCause() ?? 'error';

            return $this->json(['message' => $error, 'status' => $errorLevel], Response::HTTP_BAD_REQUEST);
        }

        return $this->json('OK');
    }
}
