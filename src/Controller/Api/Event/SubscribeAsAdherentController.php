<?php

namespace App\Controller\Api\Event;

use App\Adherent\Tag\TagEnum;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationEvent;
use App\Event\EventRegistrationFactory;
use App\Event\EventVisibilityEnum;
use App\Events;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SubscribeAsAdherentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRegistrationFactory $eventRegistrationFactory,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
    ) {
    }

    public function __invoke(Request $request, Event $event): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        if ($event->isCancelled()) {
            throw $this->createNotFoundException('Event is cancelled');
        }

        if ($event->isFinished()) {
            return $this->json(
                ['message' => 'Impossible de s\'inscrire à un événement terminé.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($request->isMethod(Request::METHOD_DELETE)) {
            /** @var EventRegistration|null $eventRegistration */
            $eventRegistration = $this->eventRegistrationRepository->findOneBy([
                'event' => $event,
                'adherent' => $adherent,
            ]);

            if ($eventRegistration) {
                if ($eventRegistration->isConfirmed()) {
                    $event->updateMembersCount(false, $adherent);

                    $eventRegistration->cancel();
                }

                if (!$event->isInvitation()) {
                    $this->entityManager->remove($eventRegistration);
                }

                $this->entityManager->flush();
            }

            return $this->json('OK', Response::HTTP_OK);
        }

        $command = new EventRegistrationCommand($event, $adherent);

        if ($event->isForAdherent()) {
            if (EventVisibilityEnum::ADHERENT === $event->visibility && !$adherent->hasTag(TagEnum::ADHERENT)) {
                return $this->json(
                    ['message' => 'Cet événement est réservé aux adhérents, adhérez pour y participer.'],
                    Response::HTTP_BAD_REQUEST
                );
            } elseif (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && !$adherent->hasTag(TagEnum::getAdherentYearTag())) {
                return $this->json(
                    ['message' => 'Cet événement est réservé aux adhérents à jour de cotisation, cotisez cette année pour y participer.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $command->updateFromRequest($request);

        $errors = $this->validator->validate($command);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $registration = $this->eventRegistrationRepository->findAdherentRegistration(
            $event->getUuidAsString(),
            $adherent->getUuidAsString(),
            null
        );

        if ($newRegistration = !$registration) {
            $this->entityManager->persist($registration = $this->eventRegistrationFactory->createFromCommand($command));
        }

        $event->updateMembersCount(true, $adherent);

        $registration->confirm();
        $registration->setSource(AppCodeEnum::VOX);

        $this->entityManager->flush();

        if ($newRegistration) {
            $this->dispatcher->dispatch(new EventRegistrationEvent($registration, true), Events::EVENT_REGISTRATION_CREATED);
        }

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
