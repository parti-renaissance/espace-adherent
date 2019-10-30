<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\StatisticsAggregator;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Mailchimp\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/adherent-message", name="app_message_common_")
 *
 * @Security("is_granted('ROLE_MESSAGE_REDACTOR')")
 */
class CommonMessageController extends AbstractController
{
    private $mailchimpCampagnUrl;
    private $mailchimpOrgId;

    public function __construct(string $mailchimpCampagnUrl, string $mailchimpOrgId)
    {
        $this->mailchimpCampagnUrl = $mailchimpCampagnUrl;
        $this->mailchimpOrgId = $mailchimpOrgId;
    }

    /**
     * @Route("/{uuid}/statistics", requirements={"uuid": "%pattern_uuid%"}, condition="request.isXmlHttpRequest()", name="statistics", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function getStatisticsAction(AbstractAdherentMessage $message, StatisticsAggregator $aggregator): Response
    {
        return $this->json($aggregator->aggregateData($message));
    }

    /**
     * @Route("/{uuid}/content", requirements={"uuid": "%pattern_uuid%"}, name="content", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function getMessageTemplateAction(AbstractAdherentMessage $message, Manager $manager): Response
    {
        return new Response($manager->getCampaignContent(current($message->getMailchimpCampaigns())));
    }

    /**
     * @Route("/{uuid}/preview-on-mailchimp", requirements={"uuid": "%pattern_uuid%"}, name="preview-on-mailchimp", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewOnMailchimpAction(AbstractAdherentMessage $message): Response
    {
        if (!$message->isSynchronized()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect(sprintf(
            '%s?u=%s&id=%s',
            $this->mailchimpCampagnUrl,
            $this->mailchimpOrgId,
            current($message->getMailchimpCampaigns())->getExternalId())
        );
    }
}
