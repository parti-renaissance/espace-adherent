<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventCommandHandler;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationCommandHandler;
use AppBundle\Form\EventCommandType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function eventsAction(string $type): Response
    {
        return $this->renderTemplate('event_manager/events_list.html.twig', [
            'events' => $this->getEvents($type),
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

        $form = $this
            ->createForm(EventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command, $this->getEventClassName());

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

    abstract protected function getEvents(string $type = null): array;

    abstract protected function getEventClassName(): string;

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
