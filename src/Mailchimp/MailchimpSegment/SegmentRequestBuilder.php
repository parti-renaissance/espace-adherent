<?php

declare(strict_types=1);

namespace App\Mailchimp\MailchimpSegment;

use App\AdherentMessage\DynamicSegmentInterface;
use App\Mailchimp\Campaign\SegmentConditionsBuilder;
use App\Mailchimp\MailchimpSegment\Request\EditSegmentRequest;

class SegmentRequestBuilder
{
    private $segmentConditionsBuilder;

    public function __construct(SegmentConditionsBuilder $segmentConditionsBuilder)
    {
        $this->segmentConditionsBuilder = $segmentConditionsBuilder;
    }

    public function createEditSegmentRequestFromDynamicSegment(DynamicSegmentInterface $segment): EditSegmentRequest
    {
        $name = \sprintf(
            '%s_%s',
            strtolower(new \ReflectionClass($segment)->getShortName()),
            $segment->getUuid()->toString()
        );

        return new EditSegmentRequest($name)
            ->setOptions($this->segmentConditionsBuilder->buildFromDynamicSegment($segment))
        ;
    }
}
