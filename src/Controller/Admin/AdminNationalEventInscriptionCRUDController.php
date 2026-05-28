<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\NationalEvent\NationalEventInscriptionsAdmin;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Form\Admin\NationalEvent\NationalEventChoiceFormType;
use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\NationalEvent\Command\SendTicketCommand;
use App\Repository\NationalEvent\NationalEventRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminNationalEventInscriptionCRUDController extends CRUDController
{
    public function __construct(private readonly NationalEventRepository $nationalEventRepository)
    {
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        /** @var NationalEventInscriptionsAdmin $admin */
        $admin = $this->admin;

        if ($eventId = $admin->getPersistentParameter('event')) {
            if (!$this->isEventAllowedForAdmin($eventId, $admin)) {
                $this->addFlash('sonata_flash_error', 'L\'événement sélectionné est introuvable ou interdit pour cet espace d\'administration.');

                return $this->redirect($admin->generateUrl('create', ['event' => null]));
            }

            return parent::createAction($request);
        }

        $allowedTypes = $admin->getAllowedEventTypes();
        $forbiddenTypes = $admin->getForbiddenEventTypes();

        $form = $this->createForm(NationalEventChoiceFormType::class, null, [
            'allowed_types' => $allowedTypes,
            'forbidden_types' => $forbiddenTypes,
            'preselected_event' => $this->nationalEventRepository->findCurrentOrNext($allowedTypes, $forbiddenTypes),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NationalEvent $event */
            $event = $form->get('event')->getData();

            return $this->redirect($admin->generateUrl('create', ['event' => $event->getId()]));
        }

        return $this->renderWithExtraParams('admin/national_event/inscription_create_choose_event.html.twig', [
            'action' => 'create',
            'form' => $form->createView(),
        ]);
    }

    private function isEventAllowedForAdmin(mixed $eventId, NationalEventInscriptionsAdmin $admin): bool
    {
        $event = $this->nationalEventRepository->find($eventId);
        if (!$event) {
            return false;
        }

        $allowed = $admin->getAllowedEventTypes();
        if (null !== $allowed && !\in_array($event->type, $allowed, true)) {
            return false;
        }

        $forbidden = $admin->getForbiddenEventTypes();
        if (null !== $forbidden && \in_array($event->type, $forbidden, true)) {
            return false;
        }

        return true;
    }

    public function sendTicketAction(Request $request, int $id, MessageBusInterface $messageBus): Response
    {
        /** @var EventInscription $inscription */
        $inscription = $this->admin->getObject($id);

        if (!$inscription) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('sendTicket', $inscription);

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
