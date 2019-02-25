<?php

namespace AppBundle\Mailchimp;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Exception\InvalidCampaignIdException;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use AppBundle\Mailchimp\Synchronisation\RequestBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const INTEREST_KEY_CP_HOST = 'CITIZEN_PROJECT_HOST';
    public const INTEREST_KEY_COMMITTEE_HOST = 'COMMITTEE_HOST';
    public const INTEREST_KEY_COMMITTEE_SUPERVISOR = 'COMMITTEE_SUPERVISOR';
    public const INTEREST_KEY_COMMITTEE_FOLLOWER = 'COMMITTEE_FOLLOWER';
    public const INTEREST_KEY_COMMITTEE_NO_FOLLOWER = 'COMMITTEE_NO_FOLLOWER';

    private $driver;
    private $requestBuildersLocator;

    public function __construct(Driver $driver, ContainerInterface $requestBuildersLocator)
    {
        $this->driver = $driver;
        $this->requestBuildersLocator = $requestBuildersLocator;
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
            $requestBuilder->buildMemberRequest($message->getEmailAddress())
        );

        if ($result) {
            $this->logger->info(sprintf('Mailchimp member "%s" has been edited', $adherent->getUuidAsString()));

            // Active/Inactive member's tags
            $result = $this->driver->updateMemberTags(
                $requestBuilder->createMemberTagsRequest($adherent->getEmailAddress(), $message->getRemovedTags())
            );

            if ($result) {
                $this->logger->info(sprintf('Mailchimp member "%s" tags have been updated', $adherent->getUuidAsString()));
            }
        }
    }

    public function getCampaignContent(AdherentMessageInterface $message): string
    {
        if (!$message->getExternalId()) {
            throw new InvalidCampaignIdException(
                sprintf('Message "%s" does not have a valid campaign id', $message->getUuid())
            );
        }

        return $this->driver->getCampaignContent($message->getExternalId());
    }

    public function editCampaign(AdherentMessageInterface $message): bool
    {
        $requestBuilder = $this->requestBuildersLocator->get(CampaignRequestBuilder::class);

        $editCampaignRequest = $requestBuilder->createEditCampaignRequestFromMessage($message);

        // When ExternalId does not exist, then it is Campaign creation
        if (!$campaignId = $message->getExternalId()) {
            $campaignData = $this->driver->createCampaign($editCampaignRequest);

            if (empty($campaignData['id'])) {
                throw new \RuntimeException(
                    sprintf('Campaign for the message "%s" has not been created', $message->getUuid())
                );
            }

            $message->setExternalId($campaignData['id']);
        } else {
            $campaignData = $this->driver->updateCampaign($campaignId, $editCampaignRequest);
        }

        if (isset($campaignData['recipients']['recipient_count'])) {
            $message->setRecipientCount($campaignData['recipients']['recipient_count']);
        }

        return true;
    }

    public function editCampaignContent(AdherentMessageInterface $message): bool
    {
        if (!$message->getExternalId()) {
            throw new InvalidCampaignIdException(
                sprintf('Message "%s" does not have a valid campaign id', $message->getUuid()->toString())
            );
        }

        $requestBuilder = $this->requestBuildersLocator->get(CampaignRequestBuilder::class);

        if (!$this->driver->editCampaignContent(
            $message->getExternalId(),
            $requestBuilder->createContentRequest($message)
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
        if (!$message->getExternalId()) {
            throw new InvalidCampaignIdException(
                sprintf('Message "%s" does not have a valid campaign id', $message->getUuid()->toString())
            );
        }

        return $this->driver->sendCampaign($message->getExternalId());
    }
}
