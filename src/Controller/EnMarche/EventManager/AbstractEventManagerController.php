<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Address\GeoCoder;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCanceledHandler;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Form\Coalition\CoalitionEventType;
use App\Form\EventCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractEventManagerController extends AbstractController
{
    use AccessDelegatorTrait;

    public const EVENTS_TYPE_ALL = 'all';
    public const EVENTS_TYPE_MINE = 'mine';

    /**
     * @Route(
     *     path="/evenements",
     *     name="_events",
     *     defaults={"type": AbstractEventManagerController::EVENTS_TYPE_ALL},
     *     methods={"GET"}
     * )
     *
     * @Route(
     *     path="/mes-evenements",
     *     name="_events_mine",
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
            'share_by_email' => $this->shareByEmail(),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="_create", methods={"GET", "POST"})
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
            ->createForm(
                CoalitionEvent::class === $this->getEventClassName() ? CoalitionEventType::class : EventCommandType::class,
                $command,
                [
                    'event_group_category' => $this->getEventGroupCategory(),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command, $this->getEventClassName());

            $registrationCommand = new EventRegistrationCommand($event, $user);
            $eventRegistrationCommandHandler->handle($registrationCommand, !$event->isCoalitionsEvent());

            return $this->renderTemplate('event_manager/event_create_success.html.twig', [
                'event' => $event,
                'share_by_email' => $this->shareByEmail(),
            ]);
        }

        return $this->renderTemplate('event_manager/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements/{slug}/modifier", name="_edit", methods={"GET", "POST"})
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function editAction(Request $request, BaseEvent $event, EventCommandHandler $handler): Response
    {
        $form = $this
            ->createForm(
                EventCommandType::class,
                $command = EventCommand::createFromEvent($event),
                [
                    'image_path' => $event->getImagePath(),
                    'coalition' => CoalitionEvent::class === $this->getEventClassName(),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handleUpdate($event, $command);

            $this->addFlash('info', 'event.update.success');

            return $this->redirectToEventManagerRoute('edit', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->renderTemplate('event_manager/event_edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements/{slug}/annuler", name="_cancel", methods={"GET"})
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function cancelAction(BaseEvent $event, EventCanceledHandler $eventCanceledHandler): Response
    {
        if (!$event->isActive()) {
            throw new BadRequestHttpException();
        }

        $eventCanceledHandler->handle($event);

        $this->addFlash('info', 'event.cancel.success');

        return $this->redirectToEventManagerRoute('events_mine');
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

    protected function redirectToEventManagerRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_{$this->getSpaceType()}_event_manager_${subName}", $parameters);
    }

    protected function getEventClassName(): string
    {
        return DefaultEvent::class;
    }

    protected function getEventGroupCategory(): ?EventGroupCategory
    {
        return null;
    }

    protected function shareByEmail(): bool
    {
        return true;
    }
}
