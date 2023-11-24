<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\EmailValidationRequest;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/persist-email', name: 'app_persist_email', methods: ['POST'])]
class PersistEmailController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $emailValidationRequest = $this->serializer->deserialize($request->getContent(), EmailValidationRequest::class, JsonEncoder::FORMAT, [
            'groups' => ['adhesion-email:persist'],
        ]);

        $errors = $this->validator->validate($emailValidationRequest, null, ['adhesion-email:persist']);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist(AdherentRequest::createForEmail($emailValidationRequest->getEmail()));
        $this->entityManager->flush();

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
