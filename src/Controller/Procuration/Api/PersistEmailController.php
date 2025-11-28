<?php

declare(strict_types=1);

namespace App\Controller\Procuration\Api;

use App\Adhesion\Request\EmailValidationRequest;
use App\Procuration\Command\PersistProcurationEmailCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/persist-email', name: 'app_procuration_persist_email', methods: ['POST'])]
class PersistEmailController extends AbstractController
{
    use HandleTrait;

    public const SESSION_KEY = 'procuration.email_identifier';

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request): Response
    {
        $emailValidationRequest = $this->serializer->deserialize($request->getContent(), EmailValidationRequest::class, JsonEncoder::FORMAT, [
            'groups' => ['procuration-email:persist'],
        ]);

        $errors = $this->validator->validate($emailValidationRequest, null, ['procuration-email:persist']);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        /** @var PersistProcurationEmailCommand $command */
        $command = $this->serializer->deserialize($request->getContent(), PersistProcurationEmailCommand::class, JsonEncoder::FORMAT);
        $command->clientIp = $request->getClientIp();

        $emailIdentifier = $this->handle($command);

        $request->getSession()->set(self::SESSION_KEY, $emailIdentifier);

        return $this->json([
            'message' => 'OK',
            'status' => 'success',
        ], Response::HTTP_CREATED);
    }
}
