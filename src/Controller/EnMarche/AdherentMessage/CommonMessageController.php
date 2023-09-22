<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\StatisticsAggregator;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adherent-message', name: 'app_message_common_')]
#[IsGranted('ROLE_MESSAGE_REDACTOR')]
class CommonMessageController extends AbstractController
{
    #[Route(path: '/{uuid}/statistics', requirements: ['uuid' => '%pattern_uuid%'], condition: 'request.isXmlHttpRequest()', name: 'statistics', methods: ['GET'])]
    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    public function getStatisticsAction(AbstractAdherentMessage $message, StatisticsAggregator $aggregator): Response
    {
        if (!$message->isMailchimp()) {
            throw $this->createNotFoundException();
        }

        return $this->json($aggregator->aggregateData($message));
    }

    #[Route(path: '/{uuid}/content', requirements: ['uuid' => '%pattern_uuid%'], name: 'content', methods: ['GET'])]
    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    public function getMessageTemplateAction(
        AbstractAdherentMessage $message,
        AdherentMessageManager $manager
    ): Response {
        return new Response($manager->getMessageContent($message));
    }

    #[Route(path: '/{uuid}/preview-on-mailchimp', requirements: ['uuid' => '%pattern_uuid%'], name: 'preview-on-mailchimp', methods: ['GET'])]
    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    public function previewOnMailchimpAction(AbstractAdherentMessage $message, MailchimpObjectIdMapping $mailchimpObjectIdMapping): Response
    {
        if (!$message->isMailchimp()) {
            throw $this->createNotFoundException();
        }

        if (!$message->isSynchronized()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($mailchimpObjectIdMapping->generateMailchimpPreviewLink($message->getMailchimpId()));
    }
}
