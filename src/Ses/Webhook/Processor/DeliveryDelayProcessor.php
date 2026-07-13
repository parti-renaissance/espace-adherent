<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\SesDeliveryDelayEvent;
use App\Ses\Webhook\SesDeliveryDelayParser;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class DeliveryDelayProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesDeliveryDelayParser $parser,
        SesEventTargetResolver $resolver,
        SendErroredRowReconciler $reconciler,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
    ) {
        parent::__construct($parser, $resolver, $reconciler);
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::DeliveryDelay === $type;
    }

    protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void
    {
        \assert($event instanceof SesDeliveryDelayEvent);

        $this->memberRepository->markDelayed($target->messageId, $target->adherentId, $event->delayedAt, $event->delayType);
    }
}
