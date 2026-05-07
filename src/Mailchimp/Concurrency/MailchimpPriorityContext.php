<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

/**
 * Per-process Mailchimp execution context: priority + currently-held slot.
 *
 * Populated by MailchimpHandlerMiddleware around each Messenger handler. The
 * throttling HTTP client reads both:
 *  - priority → which slot pool to draw from when it must acquire
 *  - held slot → if non-null, a parent already holds the slot for the whole
 *                handler, so the per-request acquire/release is skipped
 *
 * Default priority = High; outside Messenger (live HTTP, CLI) we don't penalize
 * traffic. Held slot defaults to null (no parent context).
 */
class MailchimpPriorityContext
{
    private Priority $priority = Priority::High;
    private ?MailchimpSlot $heldSlot = null;

    public function setPriority(Priority $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function setHeldSlot(?MailchimpSlot $slot): void
    {
        $this->heldSlot = $slot;
    }

    public function getHeldSlot(): ?MailchimpSlot
    {
        return $this->heldSlot;
    }

    public function hasHeldSlot(): bool
    {
        return null !== $this->heldSlot;
    }
}
