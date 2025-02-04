<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Address\GeoCoder;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCanceledHandler;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Form\EventCommandType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

abstract class AbstractEventManagerController extends AbstractController
{
    use AccessDelegatorTrait;

    public const EVENTS_TYPE_ALL = 'all';
    public const EVENTS_TYPE_MINE = 'mine';

    #[Route(path: '/evenements', name: '_events', defaults: ['type' => AbstractEventManagerController::EVENTS_TYPE_ALL], methods: ['GET'])]
    #[Route(path: '/mes-evenements', name: '_events_mine', defaults: ['type' => AbstractEventManagerController::EVENTS_TYPE_MINE], methods: ['GET'])]
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

    #[Route(path: '/evenements/creer', name: '_create', methods: ['GET', 'POST'])]
    public function eventsCreateAction(
        Request $request,
        GeoCoder $geoCoder,
        EventCommandHandler $eventCommandHandler,
    ): Response {
        /** @var Adherent $user */
        $user = $this->getMainUser($request->getSession());

        $command = new EventCommand($user);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createEventForm($command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command);

            return $this->renderTemplate('event_manager/event_create_success.html.twig', [
                'event' => $event,
                'share_by_email' => $this->shareByEmail(),
            ]);
        }

        return $this->renderTemplate('event_manager/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('HOST_EVENT', subject: 'event')]
    #[Route(path: '/evenements/{slug}/modifier', name: '_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Event $event, EventCommandHandler $handler): Response
    {
        $command = EventCommand::createFromEvent($event);

        $form = $this
            ->createEventForm($command, $event)
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

    #[IsGranted('HOST_EVENT', subject: 'event')]
    #[Route(path: '/evenements/{slug}/annuler', name: '_cancel', methods: ['GET'])]
    public function cancelAction(Event $event, EventCanceledHandler $eventCanceledHandler): Response
    {
        if (!$event->isActive()) {
            throw new BadRequestHttpException();
        }

        $eventCanceledHandler->handle($event);

        $this->addFlash('info', 'event.cancel.success');

        return $this->redirectToEventManagerRoute('events_mine');
    }

    abstract protected function getSpaceType(): string;

    protected function createEventForm(EventCommand $command, ?Event $event = null): FormInterface
    {
        return $this->createForm(
            EventCommandType::class,
            $command,
            [
                'event_group_category' => $this->getEventGroupCategory(),
                'image_path' => $event ? $event->getImagePath() : null,
                'extra_fields' => true,
            ]
        );
    }

    abstract protected function getEventsPaginator(
        Adherent $adherent,
        ?string $type = null,
        int $page = 1,
    ): PaginatorInterface;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('event_manager/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToEventManagerRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_{$this->getSpaceType()}_event_manager_{$subName}", $parameters);
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
