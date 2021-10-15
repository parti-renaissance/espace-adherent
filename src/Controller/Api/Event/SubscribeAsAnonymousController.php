<?php

namespace App\Controller\Api\Event;

use App\Entity\Event\BaseEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscribeAsAnonymousController extends AbstractController
{
    private $validator;
    private $serializer;
    private $handler;

    public function __construct(
        EventRegistrationCommandHandler $handler,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->handler = $handler;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function __invoke(Request $request, BaseEvent $event): Response
    {
        if ($event->isCancelled()) {
            throw $this->createNotFoundException('Event is cancelled');
        }

        if ($event->isPrivate()) {
            throw $this->createNotFoundException();
        }

        $this->serializer->deserialize($request->getContent(), EventRegistrationCommand::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::GROUPS => ['event_registration_write'],
            AbstractNormalizer::OBJECT_TO_POPULATE => $command = new EventRegistrationCommand($event),
        ]);

        $errors = $this->validator->validate($command);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->handler->handle(
            $command,
            $event->needNotifyForRegistration()
        );

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
