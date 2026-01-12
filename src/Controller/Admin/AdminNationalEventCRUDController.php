<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\NationalEvent\NationalEvent;
use App\JeMengage\Push\Command\NationalEventTicketAvailableNotificationCommand;
use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\NationalEvent\Command\SendTicketCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminNationalEventCRUDController extends CRUDController
{
    public function inscriptionsAction(Request $request, int $id, EventInscriptionRepository $eventInscriptionRepository): Response
    {
        /** @var NationalEvent $event */
        $event = $this->admin->getObject($id);

        if (!$event) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('inscriptions', $event);

        return $this->renderWithExtraParams('admin/national_event/inscriptions.html.twig', [
            'national_event' => $event,
            'action' => 'inscriptions',
            'event_inscriptions' => $eventInscriptionRepository->findAllForEventPaginated(
                $event,
                $request->query->get('q'),
                $statuses = InscriptionStatusEnum::APPROVED_STATUSES,
                $request->query->getInt('page', 1),
                100
            ),
            'count_without_qrcodes' => $countWithoutQRCodes = $eventInscriptionRepository->countWithoutTicketQRCodes($event),
            'notification_disabled' => $countWithoutQRCodes > 0,
            'count_without_ticket' => $eventInscriptionRepository->countTickets($event, true, $statuses),
            'count_with_ticket' => $eventInscriptionRepository->countTickets($event, false, $statuses),
            'count_by_status' => $eventInscriptionRepository->countByStatus($event),
            'count_available_for_push' => $eventInscriptionRepository->countForPush($event),
            'count_available_for_push_missing' => $eventInscriptionRepository->countForPush($event, true),
        ]);
    }

    public function sendTicketsAction(Request $request, int $id, EventInscriptionRepository $eventInscriptionRepository, MessageBusInterface $messageBus): Response
    {
        /** @var NationalEvent $event */
        $event = $this->admin->getObject($id);

        if (!$event) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('inscriptions', $event);

        $inscriptions = [];

        if ($request->query->has('all')) {
            $inscriptions = $eventInscriptionRepository->findAllPartialForEvent($event);
        } elseif ($request->query->has('only_missing')) {
            $inscriptions = $eventInscriptionRepository->findAllPartialForEvent($event, false);
        } elseif (($uuid = $request->query->get('uuid')) && $inscription = $eventInscriptionRepository->findOneByUuid($uuid)) {
            $inscriptions = [$inscription];
        }

        if ($inscriptions) {
            foreach ($inscriptions as $inscription) {
                $messageBus->dispatch(new SendTicketCommand($inscription->getUuid()));
            }

            $this->addFlash('sonata_flash_success', 'Les billets sont en cours d\'envoi.');
        } else {
            $this->addFlash('sonata_flash_error', 'Aucun billet à envoyer.');
        }

        return $this->redirect($this->admin->generateObjectUrl('inscriptions', $event));
    }

    public function sendPushAction(Request $request, int $id, MessageBusInterface $messageBus): Response
    {
        /** @var NationalEvent $event */
        $event = $this->admin->getObject($id);

        if (!$event) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('inscriptions', $event);

        $type = 'only_missing';
        if ($request->query->has('all')) {
            $type = 'all';
        } elseif ($uuid = $request->query->get('uuid')) {
            $type = $uuid;
        }

        $messageBus->dispatch(new NationalEventTicketAvailableNotificationCommand($event->getUuid(), $type));

        $this->addFlash('sonata_flash_success', 'Les notifications sont en cours d\'envoi.');

        return $this->redirect($this->admin->generateObjectUrl('inscriptions', $event));
    }

    public function generateTicketQRCodesAction(int $id, EventInscriptionRepository $eventInscriptionRepository, MessageBusInterface $messageBus): Response
    {
        /** @var NationalEvent $event */
        $event = $this->admin->getObject($id);

        if (!$event) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('inscriptions', $event);

        $inscriptions = $eventInscriptionRepository->findAllWithoutTickets($event);

        foreach ($inscriptions as $inscription) {
            $messageBus->dispatch(new GenerateTicketQRCodeCommand($inscription->getUuid()));
        }

        $this->addFlash('sonata_flash_success', 'Les codes QR des billets sont en cours de génération.');

        return $this->redirect($this->admin->generateObjectUrl('inscriptions', $event));
    }
}
