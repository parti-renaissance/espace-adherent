<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCanceledHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
