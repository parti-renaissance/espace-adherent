<?php

namespace App\Controller\Admin;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\NationalEvent\Command\SendTicketCommand;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminNationalEventInscriptionCRUDController extends CRUDController
{
    public function sendTicketAction(Request $request, EventInscription $inscription, MessageBusInterface $messageBus): Response
    {
        if (!$inscription->isApproved()) {
            $this->addFlash('sonata_flash_error', 'Le statut de l\'inscription doit être "approuvée" pour envoyer un billet.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        if (!$inscription->ticketQRCodeFile) {
            if (3 < $retry = $request->query->getInt('retry')) {
                $this->addFlash('sonata_flash_error', 'Échec de la génération du code QR du billet, le billet ne peut pas être envoyé.');

                return $this->redirect($this->admin->generateUrl('list'));
            }

            $messageBus->dispatch(new GenerateTicketQRCodeCommand($inscription->getUuid()));

            sleep(2);

            return $this->redirect($this->admin->generateObjectUrl('sendTicket', $inscription, ['retry' => $retry + 1]));
        }

        if ($inscription->isTicketReady()) {
            $messageBus->dispatch(new SendTicketCommand($inscription->getUuid()));

            $this->addFlash('sonata_flash_success', 'Le billet est en cours d\'envoi.');
        } else {
            $this->addFlash('sonata_flash_error', 'Le statut invalid ou billet manquant, le billet ne peut pas être envoyé.');
        }

        return $this->redirect($this->admin->generateUrl('list'));
    }
}
