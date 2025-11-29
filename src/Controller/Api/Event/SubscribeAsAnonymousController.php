<?php

declare(strict_types=1);

namespace App\Controller\Api\Event;

use App\Entity\Event\Event;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscribeAsAnonymousController extends AbstractController
{
    public function __construct(
        private readonly EventRegistrationCommandHandler $handler,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$event->isPublic()) {
            throw $this->createNotFoundException('Event is not public');
        }

        if ($event->isCancelled()) {
            throw $this->createNotFoundException('Event is cancelled');
        }

        $this->serializer->deserialize($request->getContent(), EventRegistrationCommand::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::GROUPS => ['event_registration_write'],
            AbstractNormalizer::OBJECT_TO_POPULATE => $command = new EventRegistrationCommand($event),
        ]);

        $errors = $this->validator->validate($command, groups: ['registration_public']);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $command->setAdherent($this->adherentRepository->findOneForMatching($command->getEmailAddress(), $command->getFirstName(), $command->getLastName()));

        $this->handler->handle($command, $event->needNotifyForRegistration());

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
