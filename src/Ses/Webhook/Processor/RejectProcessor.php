<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use App\Ses\Webhook\SesRejectEvent;
use App\Ses\Webhook\SesRejectParser;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class RejectProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesRejectParser $parser,
        SesEventTargetResolver $resolver,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
    ) {
        parent::__construct($parser, $resolver);
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::Reject === $type;
    }

    protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void
    {
        \assert($event instanceof SesRejectEvent);

        $this->memberRepository->markRejected($target->messageId, $target->adherentId, $event->rejectedAt, $event->reason);
    }
}
