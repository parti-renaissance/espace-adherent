<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class AdminAdherentMessageCRUDController extends CRUDController
{
    public function __construct(private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping)
    {
    }

    public function displayAction(AdherentMessage $adherentMessage): Response
    {
        $this->admin->checkAccess('display');

        if (!$adherentMessage->getMailchimpId()) {
            return $this->redirectToList();
        }

        $previewLink = $this->mailchimpObjectIdMapping->generateMailchimpPreviewLink($adherentMessage->getMailchimpId());

        return $this->redirect($previewLink);
    }
}
