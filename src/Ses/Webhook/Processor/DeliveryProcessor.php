<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\SesDeliveryEvent;
use App\Ses\Webhook\SesDeliveryParser;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class DeliveryProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesDeliveryParser $parser,
        SesEventTargetResolver $resolver,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
    ) {
        parent::__construct($parser, $resolver);
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::Delivery === $type;
    }

    protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void
    {
        \assert($event instanceof SesDeliveryEvent);

        $this->memberRepository->markDelivered($target->messageId, $target->adherentId, $event->deliveredAt);
    }
}
