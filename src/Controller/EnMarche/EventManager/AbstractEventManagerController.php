<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Address\GeoCoder;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Event;
use App\Entity\EventGroupCategory;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Form\EventCommandType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractEventManagerController extends AbstractController
{
    use AccessDelegatorTrait;

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
            'events' => $this->getEventsPaginator(
                $this->getMainUser($request->getSession()),
                $type,
                $request->query->getInt('page', 1)
            ),
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
        $user = $this->getMainUser($request->getSession());

        $command = new EventCommand($user);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(EventCommandType::class, $command, ['event_group_category' => $this->getEventGroupCategory()])
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

    abstract protected function getEventsPaginator(
        Adherent $adherent,
        string $type = null,
        int $page = 1
    ): PaginatorInterface;

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

    protected function getEventGroupCategory(): ?EventGroupCategory
    {
        return null;
    }
}
