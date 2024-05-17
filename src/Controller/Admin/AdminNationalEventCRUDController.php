<?php

namespace App\Controller\Admin;

use App\Entity\NationalEvent\NationalEvent;
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
    public function inscriptionsAction(Request $request, NationalEvent $event, EventInscriptionRepository $eventInscriptionRepository): Response
    {
        return $this->renderWithExtraParams('admin/national_event/inscriptions.html.twig', [
            'national_event' => $event,
            'action' => 'inscriptions',
            'event_inscriptions' => $eventInscriptionRepository->findAllForEventPaginated(
                $event,
                $request->query->get('q'),
                $statuses = [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE],
                $request->query->getInt('page', 1),
                100
            ),
            'count_without_qrcodes' => $eventInscriptionRepository->countWithoutTicketQRCodes($event),
            'count_without_ticket' => $eventInscriptionRepository->countTickets($event, true, $statuses),
            'count_with_ticket' => $eventInscriptionRepository->countTickets($event, false, $statuses),
            'count_by_status' => $eventInscriptionRepository->countByStatus($event),
        ]);
    }

    public function sendTicketsAction(Request $request, NationalEvent $event, EventInscriptionRepository $eventInscriptionRepository, MessageBusInterface $messageBus): Response
    {
        $inscriptions = [];

        if ($request->query->has('all')) {
            $inscriptions = $eventInscriptionRepository->findAllPartialForEvent($event, [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE]);
        } elseif ($request->query->has('only_missing')) {
            $inscriptions = $eventInscriptionRepository->findAllPartialForEvent($event, [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE], true);
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

    public function generateTicketQRCodesAction(NationalEvent $event, EventInscriptionRepository $eventInscriptionRepository, MessageBusInterface $messageBus): Response
    {
        $inscriptions = $eventInscriptionRepository->findAllWithoutTickets($event);

        foreach ($inscriptions as $inscription) {
            $messageBus->dispatch(new GenerateTicketQRCodeCommand($inscription->getUuid()));
        }

        $this->addFlash('sonata_flash_success', 'Les codes QR des billets sont en cours de génération.');

        return $this->redirect($this->admin->generateObjectUrl('inscriptions', $event));
    }
}
