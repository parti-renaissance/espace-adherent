<?php

namespace App\Controller\Api;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventSubscribeController extends AbstractController
{
    private $entityManager;
    private $eventRegistrationFactory;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRegistrationFactory $eventRegistrationFactory,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->validator = $validator;
    }

    public function __invoke(Request $request, BaseEvent $event, UserInterface $adherent): Response
    {
        if ($request->isMethod(Request::METHOD_DELETE)) {
            $eventRegistration = $this->entityManager->getRepository(EventRegistration::class)->findOneBy([
                'event' => $event,
                'adherentUuid' => $adherent->getUuid(),
            ]);

            if ($eventRegistration) {
                $event->decrementParticipantsCount();
                $this->entityManager->remove($eventRegistration);
                $this->entityManager->flush();
            }

            return $this->json('OK', Response::HTTP_OK);
        }

        $errors = $this->validator->validate($command = new EventRegistrationCommand($event, $adherent));

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $event->incrementParticipantsCount();
        $this->entityManager->persist($this->eventRegistrationFactory->createFromCommand($command));
        $this->entityManager->flush();

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
