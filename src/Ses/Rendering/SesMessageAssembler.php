<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailer\Message\Renaissance\AdherentMessage\CampaignPublicationMessage;
use App\Mailer\Template\Manager;

/**
 * Assembles, once per publication, the message-level HTML of a campaign email from the DB template
 * system (chrome parent + child body, via Manager) without going through Mailchimp.
 *
 * In the Mailchimp/Mandrill flow the {{var}} placeholders were substituted by the provider; Manager
 * only injects the child body into the parent slot and resolves show_if: blocks. This assembler
 * therefore resolves the message-level variables (body content + reply-to buttons, provided by the
 * AbstractRenaissanceAdherentMessage base) app-side. Recipient-level Dictionary codes are left
 * untouched here and resolved per recipient in Phase 5.
 */
class SesMessageAssembler
{
    public function __construct(
        private readonly Manager $templateManager,
        private readonly EmailCssInliner $cssInliner,
        private readonly PreheaderExtractor $preheaderExtractor,
    ) {
    }

    public function assemble(AdherentMessageInterface $adherentMessage): AssembledCampaignEmail
    {
        $message = CampaignPublicationMessage::create($adherentMessage, []);

        $template = $this->templateManager->findTemplateForMessage($message);

        if (!$template) {
            throw new \RuntimeException(\sprintf('No email template found for "%s".', CampaignPublicationMessage::class));
        }

        // Message-level variables come from the live message base (content + reply-to buttons).
        $vars = $message->getTemplateContent();

        // Manager injects the child body into the parent slot and hides empty show_if: blocks. It
        // does NOT substitute {{var}} when $fillVariables is false.
        $html = $this->templateManager->getTemplateContent($template, false, $vars);

        // Substitute the message-level {{var}} placeholders. Dictionary codes such as {{Prénom}} are
        // not present in $vars, so they survive for the per-recipient pass (Phase 5).
        $html = strtr($html, $this->buildPlaceholderReplacements($vars));

        // Inline the margin reset (Gmail strips <head><style>; SES is a pure transport and does not inline).
        $html = $this->cssInliner->inline($html);

        // Inject the hidden preview line (preheader) from the message content, once per publication.
        $preheader = $this->preheaderExtractor->extract($adherentMessage->getContent());
        if ('' !== $preheader) {
            $html = $this->injectPreheader($html, $preheader);
        }

        $sender = $template->getEffectiveSender();

        return new AssembledCampaignEmail(
            $html,
            (string) $adherentMessage->getSubject(),
            $sender?->email ?? '',
            $sender?->name,
            $message->getReplyTo(),
            campaignUuid: $adherentMessage->getUuid()->toRfc4122(),
        );
    }

    private function injectPreheader(string $html, string $preheader): string
    {
        if (!preg_match('/<body[^>]*>/i', $html, $matches, \PREG_OFFSET_CAPTURE)) {
            return $html;
        }

        $block = \sprintf(
            '<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">%s</div>',
            htmlspecialchars($preheader, \ENT_QUOTES)
        );

        $insertAt = $matches[0][1] + \strlen($matches[0][0]);

        return substr_replace($html, $block, $insertAt, 0);
    }

    private function buildPlaceholderReplacements(array $vars): array
    {
        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements[\sprintf('{{%s}}', $key)] = (string) $value;
        }

        return $replacements;
    }
}
