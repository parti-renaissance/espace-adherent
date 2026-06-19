<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use App\AdherentMessage\Variable\Parser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Ses\Client\SesEmail;

/**
 * Produces the final, personalised SesEmail for one recipient from the message-level HTML assembled
 * once per publication (SesMessageAssembler). Only the recipient-level Dictionary codes are resolved
 * here; the chrome, body and source sections were already resolved in Phase 4.
 */
class SesRecipientEmailFactory
{
    public function __construct(
        private readonly Parser $parser,
        private readonly SesVariableRenderer $variableRenderer,
        private readonly SesRecipientContextFactory $contextFactory,
    ) {
    }

    public function create(AssembledCampaignEmail $assembled, SesRecipient $recipient): SesEmail
    {
        $variables = $this->parser->extract($assembled->html);
        $context = $this->contextFactory->create($recipient);

        $html = $this->variableRenderer->render($assembled->html, $variables, $context);

        return new SesEmail(
            $recipient->email,
            $assembled->subject,
            $html,
            $assembled->fromEmail,
            $assembled->fromName,
            $assembled->replyTo,
        );
    }
}
