<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Synchronisation\Command\UpdateCauseMailchimpIdCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class CoalitionsConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof CoalitionsFilter;
    }

    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        /** @var CoalitionsFilter $filter */
        if (!$filter = $campaign->getMessage()->getFilter()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Coalition message should have a filter');
        }

        $cause = $filter->getCause();
        if (!$cause->getMailchimpId()) {
            $this->bus->dispatch(new UpdateCauseMailchimpIdCommand($cause));
        }

        return $cause->getMailchimpId();
    }

    /** @required */
    public function setBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }
}
