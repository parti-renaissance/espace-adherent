<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;

interface SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool;

    public function build(MailchimpCampaign $campaign): array;
}
