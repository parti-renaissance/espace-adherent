<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventCommandHandler;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationCommandHandler;
use AppBundle\Form\EventCommandType;
use AppBundle\Referent\ManagedEventsExporter;
use AppBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractEventManagerController extends Controller
{
    /**
     * @Route("/evenements", name="events", methods={"GET"})
     */
    public function eventsAction(EventRepository $eventRepository, ManagedEventsExporter $eventsExporter): Response
    {
        if (EventManagerSpaceEnum::REFERENT === $this->getSpaceType()) {
            $events = $eventRepository->findManagedBy($this->getUser());
        } else {
            $events = $eventRepository->findEventsByOrganizer($this->getUser());
        }

        return $this->renderTemplate('event_manager/events_list.html.twig', [
            'eventsAsJson' => $eventsExporter->exportAsJson($events),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="events_create", methods={"GET", "POST"})
     */
    public function eventsCreateAction(
        Request $request,
        GeoCoder $geoCoder,
        EventCommandHandler $eventCommandHandler,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        $command = new EventCommand($user);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $eventRegistrationCommandHandler->handle($registrationCommand);

            return $this->renderTemplate('event_manager/event_create.html.twig', [
                'event' => $event,
            ]);
        }

        return $this->renderTemplate('event_manager/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    abstract protected function getSpaceType(): string;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('event_manager/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToJecouteRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_event_manager_{$this->getSpaceType()}_${subName}", $parameters);
    }
}
