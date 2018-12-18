<?php

namespace AppBundle\Mailchimp;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Exception\InvalidCampaignIdException;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentCommandInterface;
use AppBundle\Mailchimp\Synchronisation\RequestBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    public function editMember(Adherent $adherent, AdherentCommandInterface $message): void
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
                $requestBuilder->createMemberTagsRequest($message->getEmailAddress(), $message->getRemovedTags())
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

        if (!$campaignId = $message->getExternalId()) {
            if (!$campaignId = $this->driver->createCampaign($editCampaignRequest)) {
                throw new \RuntimeException(
                    sprintf('Campaign for the message "%s" has not been created', $message->getUuid())
                );
            }

            $message->setExternalId($campaignId);

            return true;
        }

        return $this->driver->updateCampaign($campaignId, $editCampaignRequest);
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
}
