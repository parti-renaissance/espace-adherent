<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Manager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Send-time provisioning of the real Mailchimp channel, used only for the campaigns routed to Mailchimp
 * (MailchimpCampaign::$sendViaMailchimp, set at audience preparation when the recipient count exceeds
 * PUBLICATION_SEND_VIA_MAILCHIMP_THRESHOLD — while the AWS SES account is capped). Reactivates the dormant
 * Mailchimp operations (remote segment + campaign + content) without restoring the removed edit-time
 * synchronisation machinery.
 */
class MailchimpChannelInitializer
{
    public function __construct(
        private readonly StaticSegmentInitializer $staticSegmentInitializer,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly Manager $mailchimpManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Order is mandatory: the remote static segment (staticSegmentId) must exist BEFORE
     * editCampaign(), because the campaign request builder reads staticSegmentId to attach the
     * saved_segment_id. Creating the campaign first would produce a remote campaign without a
     * segment and MailchimpCampaignSendGuard would abort at send time.
     */
    public function ensureRemoteChannel(MailchimpCampaign $campaign): void
    {
        $this->staticSegmentInitializer->ensureLocalSegment($campaign);
        $segment = $campaign->getMailchimpStaticSegment();

        if (null === $campaign->getStaticSegmentId()) {
            $name = $segment->name ?? \sprintf('PROD_%s', $campaign->getMessage()->getUuid()->toRfc4122());
            // Same list as the audience push (ProcessAudienceChunkHandler pushes to getMainListId()):
            // segment and push must target the same list, otherwise the push hits a missing segment.
            $listId = $this->mailchimpObjectIdMapping->getMainListId();

            $segmentId = $this->staticSegmentService->create($name, [], $listId);
            if (null === $segmentId) {
                throw new \RuntimeException(\sprintf('Failed to create Mailchimp static segment for campaign %d.', $campaign->getId()));
            }

            $campaign->setStaticSegmentId($segmentId);
            $segment->mailchimpSegmentId = $segmentId;
        }

        // editCampaign() is idempotent on externalId (create-if-null, else update). Flush right
        // after so the externalId is persisted before the content push (bounds the orphan-campaign
        // window if the content push then fails and the message is retried).
        $this->mailchimpManager->editCampaign($campaign);
        $this->entityManager->flush();

        $this->mailchimpManager->editCampaignContent($campaign);
    }
}
