<?php

namespace App\Controller\Api\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationEvent;
use App\Event\EventRegistrationFactory;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SubscribeAsAdherentController extends AbstractController
{
    private $entityManager;
    private $eventRegistrationFactory;
    private $validator;
    private $dispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRegistrationFactory $eventRegistrationFactory,
        ValidatorInterface $validator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->validator = $validator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param UserInterface|Adherent $adherent
     */
    public function __invoke(Request $request, BaseEvent $event, UserInterface $adherent): Response
    {
        if ($event->isCancelled()) {
            throw $this->createNotFoundException('Event is cancelled');
        }

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

        $this->entityManager->persist($registration = $this->eventRegistrationFactory->createFromCommand($command));
        $registration->setSource($adherent->getAuthAppCode());
        $event->incrementParticipantsCount();
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EventRegistrationEvent(
            $registration,
            $event->getSlug(),
            true
        ), Events::EVENT_REGISTRATION_CREATED);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
