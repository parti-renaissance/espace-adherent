<?php

namespace App\Controller\EnMarche\EventManager;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Event;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Form\EventCommandType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractEventManagerController extends Controller
{
    public const EVENTS_TYPE_ALL = 'all';
    public const EVENTS_TYPE_MINE = 'mine';

    /**
     * @Route(
     *     path="/evenements",
     *     name="events",
     *     defaults={"type": AbstractEventManagerController::EVENTS_TYPE_ALL},
     *     methods={"GET"}
     * )
     *
     * @Route(
     *     path="/mes-evenements",
     *     name="events_mine",
     *     defaults={"type": AbstractEventManagerController::EVENTS_TYPE_MINE},
     *     methods={"GET"}
     * )
     */
    public function eventsAction(Request $request, string $type): Response
    {
        return $this->renderTemplate('event_manager/events_list.html.twig', [
            'events' => $this->getEvents($this->getMainUser($request), $type),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="events_create", methods={"GET", "POST"})
     */
    public function eventsCreateAction(
        Request $request,
        GeoCoder $geoCoder,
        EventCommandHandler $eventCommandHandler,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler
    ): Response {
        /** @var Adherent $user */
        $user = $this->getMainUser($request);

        $command = new EventCommand($user);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(EventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command, $this->getEventClassName());

            $registrationCommand = new EventRegistrationCommand($event, $user);
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

    abstract protected function getEvents(Adherent $adherent, string $type = null): array;

    protected function getMainUser(Request $request)
    {
        return $this->getUser();
    }

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

    protected function getEventClassName(): string
    {
        return Event::class;
    }
}
