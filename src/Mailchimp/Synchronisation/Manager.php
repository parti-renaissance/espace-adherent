<?php

namespace AppBundle\Mailchimp\Synchronisation;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Message\AdherentMessageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $driver;
    private $interestIds;

    public function __construct(Driver $driver, array $interestIds)
    {
        $this->driver = $driver;
        $this->interestIds = $interestIds;
    }

    /**
     * Creates/updates a Mailchimp member
     */
    public function editMember(Adherent $adherent, AdherentMessageInterface $message): void
    {
        $requestBuilder = RequestBuilder::createFromAdherent($adherent, $this->interestIds);

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->getEmailAddress())
        );

        if ($result) {
            // Active/Inactive member's tags
            $result = $this->driver->updateMemberTags(
                $requestBuilder->createMemberTagsRequest($message->getEmailAddress(), $message->getRemovedTags())
            );

            if ($result) {
                $this->logger->info(sprintf('Mailchimp member "%s" has been updated', $adherent->getUuidAsString()));
            }
        }
    }
}
