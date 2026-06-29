<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use App\AdherentMessage\Variable\Parser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Ses\Client\SesEmail;
use App\Ses\Unsubscribe\UnsubscribeUrlGenerator;

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
        private readonly UnsubscribeUrlGenerator $unsubscribeUrlGenerator,
    ) {
    }

    public function create(AssembledCampaignEmail $assembled, SesRecipient $recipient): SesEmail
    {
        $variables = $this->parser->extract($assembled->html);
        $context = $this->contextFactory->create($recipient);

        $html = $this->variableRenderer->render($assembled->html, $variables, $context);

        // {{unsubscribe_url}} is a system placeholder, not a Dictionary code (the Parser does not extract
        // it): resolve it directly per recipient and feed the same URL to the List-Unsubscribe header.
        $unsubscribeUrl = $this->unsubscribeUrlGenerator->generate($recipient->uuid);
        $html = strtr($html, ['{{unsubscribe_url}}' => $unsubscribeUrl]);

        return new SesEmail(
            $recipient->email,
            $assembled->subject,
            $html,
            $assembled->fromEmail,
            $assembled->fromName,
            $assembled->replyTo,
            $unsubscribeUrl,
            campaignUuid: $assembled->campaignUuid,
            adherentUuid: $recipient->uuid,
        );
    }
}
