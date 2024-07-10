<?php

namespace App\Controller\Admin;

use App\Entity\Email;
use App\Mailer\Command\AsyncSendMessageCommand;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminEmailCRUDController extends CRUDController
{
    public function resendAction(MessageBusInterface $bus): Response
    {
        /** @var Email $email */
        $email = $this->admin->getSubject();

        $this->admin->checkAccess('resend', $email);

        $bus->dispatch(new AsyncSendMessageCommand($email->getUuid(), true));

        $this->addFlash('sonata_flash_success', 'Email a été renvoyé');

        return $this->redirectToList();
    }
}
