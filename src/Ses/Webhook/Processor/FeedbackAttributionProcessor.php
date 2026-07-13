<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use App\Ses\Webhook\SesFeedbackAttributionEvent;
use App\Ses\Webhook\SesFeedbackAttributionParser;
use App\Ses\Webhook\SesFeedbackType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class FeedbackAttributionProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesFeedbackAttributionParser $parser,
        SesEventTargetResolver $resolver,
        SendErroredRowReconciler $reconciler,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
    ) {
        parent::__construct($parser, $resolver, $reconciler);
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::Bounce === $type || SesEventType::Complaint === $type;
    }

    protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void
    {
        \assert($event instanceof SesFeedbackAttributionEvent);

        if (SesFeedbackType::HARD_BOUNCE === $event->type) {
            $this->memberRepository->markBounced($target->messageId, $target->adherentId, $event->occurredAt, $event->bounceSubType);
        } else {
            $this->memberRepository->markComplained($target->messageId, $target->adherentId, $event->occurredAt);
        }
    }
}
