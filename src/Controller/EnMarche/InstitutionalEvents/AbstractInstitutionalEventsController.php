<?php

namespace App\Controller\EnMarche\InstitutionalEvents;

use App\Address\GeoCoder;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Event\InstitutionalEvent;
use App\Form\InstitutionalEventCommandType;
use App\InstitutionalEvent\InstitutionalEventCommand;
use App\InstitutionalEvent\InstitutionalEventCommandHandler;
use App\Repository\InstitutionalEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractInstitutionalEventsController extends AbstractController
{
    use AccessDelegatorTrait;

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function institutionalEventsAction(
        Request $request,
        InstitutionalEventRepository $institutionalEventRepository
    ): Response {
        return $this->renderTemplate('institutional_events/list.html.twig', [
            'institutional_events' => $institutionalEventRepository->findByOrganizer($this->getMainUser($request->getSession())),
        ]);
    }

    #[Route(path: '/creer', name: 'create', methods: ['GET', 'POST'])]
    public function institutionalEventsCreateAction(
        Request $request,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler,
        GeoCoder $geoCoder
    ): Response {
        $command = new InstitutionalEventCommand($this->getMainUser($request->getSession()));
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(InstitutionalEventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handle($command);

            $this->addFlash('info', 'referent.institutional_event.create.success');

            return $this->redirectToInstitutionalEventsRoute('list');
        }

        return $this->renderTemplate('institutional_events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{uuid}/editer', name: 'edit', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHOR_OF', subject: 'institutionalEvent')]
    public function institutionalEventsEditAction(
        Request $request,
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $form = $this
            ->createForm(
                InstitutionalEventCommandType::class,
                $command = InstitutionalEventCommand::createFromInstitutionalEvent($institutionalEvent),
                [
                    'view' => InstitutionalEventCommandType::EDIT_VIEW,
                    'image_path' => $institutionalEvent->getImagePath(),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handleUpdate($command, $institutionalEvent);

            $this->addFlash('info', 'referent.institutional_event.update.success');

            return $this->redirectToInstitutionalEventsRoute('list');
        }

        return $this->renderTemplate('institutional_events/create.html.twig', [
            'event' => $institutionalEvent,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{uuid}/supprimer', name: 'delete', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    #[IsGranted('IS_AUTHOR_OF', subject: 'institutionalEvent')]
    public function institutionalEventsDeleteAction(
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $institutionalEventCommandHandler->handleDelete($institutionalEvent);

        $this->addFlash('info', 'referent.institutional_event.delete.success');

        return $this->redirectToInstitutionalEventsRoute('list');
    }

    abstract protected function getSpaceType(): string;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('institutional_events/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToInstitutionalEventsRoute(string $action)
    {
        return $this->redirectToRoute(sprintf('app_%s_institutional_events_%s', $this->getSpaceType(), $action));
    }
}
