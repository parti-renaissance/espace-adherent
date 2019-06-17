<?php

namespace AppBundle\Mailchimp;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailchimp\Campaign\CampaignContentRequestBuilder;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Exception\InvalidCampaignIdException;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use AppBundle\Mailchimp\Synchronisation\RequestBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const INTEREST_KEY_CP_HOST = 'CITIZEN_PROJECT_HOST';
    public const INTEREST_KEY_COMMITTEE_HOST = 'COMMITTEE_HOST';
    public const INTEREST_KEY_COMMITTEE_SUPERVISOR = 'COMMITTEE_SUPERVISOR';
    public const INTEREST_KEY_COMMITTEE_FOLLOWER = 'COMMITTEE_FOLLOWER';
    public const INTEREST_KEY_COMMITTEE_NO_FOLLOWER = 'COMMITTEE_NO_FOLLOWER';
    public const INTEREST_KEY_REFERENT = 'REFERENT';
    public const INTEREST_KEY_DEPUTY = 'DEPUTY';
    public const INTEREST_KEY_REC = 'REC';
    public const INTEREST_KEY_COORDINATOR = 'COORDINATOR';
    public const INTEREST_KEY_PROCURATION_MANAGER = 'PROCURATION_MANAGER';
    public const INTEREST_KEY_ASSESSOR_MANAGER = 'ASSESSOR_MANAGER';
    public const INTEREST_KEY_BOARD_MEMBER = 'BOARD_MEMBER';

    private $driver;
    private $requestBuildersLocator;
    private $eventDispatcher;
    private $mailchimpObjectIdMapping;

    public function __construct(
        Driver $driver,
        ContainerInterface $requestBuildersLocator,
        EventDispatcherInterface $eventDispatcher,
        MailchimpObjectIdMapping $mailchimpObjectIdMapping
    ) {
        $this->driver = $driver;
        $this->requestBuildersLocator = $requestBuildersLocator;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    /**
     * Creates/updates a Mailchimp member
     */
    public function editMember(Adherent $adherent, AdherentChangeCommandInterface $message): void
    {
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromAdherent($adherent)
        ;

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->getEmailAddress()),
            $this->mailchimpObjectIdMapping->getMainListId()
        );

        if ($result) {
            // Active/Inactive member's tags
            $this->driver->updateMemberTags(
                $requestBuilder->createMemberTagsRequest($adherent->getEmailAddress(), $message->getRemovedTags())
            );
        }
    }

    public function editNewsletterMember(NewsletterSubscription $newsletter): bool
    {
        return $this
            ->driver
            ->editMember(
                $this->requestBuildersLocator
                    ->get(RequestBuilder::class)
                    ->updateFromNewsletterSubscription($newsletter)
                    ->buildMemberRequest($newsletter->getEmail()),
                $this->mailchimpObjectIdMapping->getNewsletterListId()
            )
        ;
    }

    public function editApplicationRequestCandidate(ApplicationRequest $applicationRequest): bool
    {
        return $this
            ->driver
            ->editMember(
                $this->requestBuildersLocator
                    ->get(RequestBuilder::class)
                    ->updateFromApplicationRequest($applicationRequest)
                    ->buildMemberRequest($applicationRequest->getEmailAddress()),
                $this->mailchimpObjectIdMapping->getApplicationRequestCandidateListId()
            )
        ;
    }

    public function getCampaignContent(MailchimpCampaign $campaign): string
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(
                sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid())
            );
        }

        return $this->driver->getCampaignContent($campaign->getExternalId());
    }

    public function editCampaign(MailchimpCampaign $campaign): bool
    {
        $message = $campaign->getMessage();
        $requestBuilder = $this->requestBuildersLocator->get(CampaignRequestBuilder::class);

        $editCampaignRequest = $requestBuilder->createEditCampaignRequestFromMessage($campaign);

        $this->eventDispatcher->dispatch(Events::CAMPAIGN_PRE_EDIT, new RequestEvent($message, $editCampaignRequest));

        // When ExternalId does not exist, then it is Campaign creation
        if (!$campaignId = $campaign->getExternalId()) {
            $campaignData = $this->driver->createCampaign($editCampaignRequest);

            if (empty($campaignData['id'])) {
                throw new \RuntimeException(
                    sprintf('Campaign for the message "%s" has not been created', $message->getUuid())
                );
            }

            $campaign->setExternalId($campaignData['id']);
        } else {
            $campaignData = $this->driver->updateCampaign($campaignId, $editCampaignRequest);
        }

        if (isset($campaignData['recipients']['recipient_count'])) {
            $campaign->setRecipientCount($campaignData['recipients']['recipient_count']);
        }

        return true;
    }

    public function editCampaignContent(MailchimpCampaign $campaign): bool
    {
        $this->checkMessageExternalId($campaign);

        /** @var CampaignContentRequestBuilder $requestBuilder */
        $contentRequestBuilder = $this->requestBuildersLocator->get(CampaignContentRequestBuilder::class);

        if (!$this->driver->editCampaignContent(
            $campaign->getExternalId(),
            $contentRequestBuilder->createContentRequest($message = $campaign->getMessage())
        )) {
            $this->logger->warning(
                sprintf('Campaign content of "%s" message has not been modified', $message->getUuid()->toString())
            );

            return false;
        }

        return true;
    }

    public function deleteCampaign(string $campaignId): void
    {
        if (!$this->driver->deleteCampaign($campaignId)) {
            $this->logger->warning(sprintf('Campaign "%s" has not be deleted', $campaignId));
        }
    }

    public function sendCampaign(AdherentMessageInterface $message): bool
    {
        foreach ($message->getMailchimpCampaigns() as $campaign) {
            $this->checkMessageExternalId($campaign);
        }

        $globalStatus = false;

        foreach ($message->getMailchimpCampaigns() as $campaign) {
            $success = $this->driver->sendCampaign($campaign->getExternalId());

            $globalStatus |= $success;

            $success ? $campaign->markAsSent() : $campaign->markAsError($this->driver->getLastError());
        }

        return $globalStatus;
    }

    public function sendTestCampaign(AdherentMessageInterface $message, array $emails): bool
    {
        $campaign = current($message->getMailchimpCampaigns());

        $this->checkMessageExternalId($campaign);

        return $this->driver->sendTestCampaign($campaign->getExternalId(), $emails);
    }

    public function createStaticSegment(string $name): ?int
    {
        return $this->driver->createStaticSegment($name)['id'] ?? null;
    }

    public function addMemberToStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->pushSegmentMember($segmentId, $mail);
    }

    public function removeMemberFromStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->deleteSegmentMember($segmentId, $mail);
    }

    public function deleteMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getMainListId());
    }

    public function deleteNewsletterMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getNewsletterListId());
    }

    public function getReportData(MailchimpCampaign $campaign): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportData($campaign->getExternalId());
    }

    private function checkMessageExternalId(MailchimpCampaign $campaign): void
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(
                sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid()->toString())
            );
        }
    }
}
