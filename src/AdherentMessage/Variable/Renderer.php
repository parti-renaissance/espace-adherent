<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable;

use App\AdherentMessage\Variable\Renderer\MailchimpVariableRenderer;
use App\AdherentMessage\Variable\Renderer\PublicationVariableRendererInterface;
use App\AdherentMessage\Variable\Renderer\TipTapVariableRenderer;
use App\Entity\Adherent;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

class Renderer
{
    public function __construct(
        private readonly Parser $parser,
        private readonly ContextBuilder $contextBuilder,
        #[TaggedLocator('app.publication.variable.renderer', defaultIndexMethod: 'getFormat')]
        private readonly ServiceProviderInterface $renderersRegistry,
    ) {
    }

    public function renderMailchimp(string $content): string
    {
        return $this->render($content, null, MailchimpVariableRenderer::getFormat());
    }

    public function renderTipTap(string $content, Adherent $currentUser): string
    {
        return $this->render($content, $currentUser, TipTapVariableRenderer::getFormat());
    }

    /**
     * Resolves the Dictionary codes of a plain-text string (e.g. a subject) to the current user's
     * concrete values. Kept in the facade (Parser + ContextBuilder + strtr) rather than dispatching
     * to a transport-format renderer, to avoid coupling a preview to the SES/Mailchimp formats.
     */
    public function renderPlain(string $content, Adherent $currentUser): string
    {
        if (!$variables = $this->parser->extract($content)) {
            return $content;
        }

        return strtr($content, $this->contextBuilder->build($variables, $currentUser));
    }

    private function render(string $content, ?Adherent $currentUser, string $format): string
    {
        if (!$variables = $this->parser->extract($content)) {
            return $content;
        }

        /** @var PublicationVariableRendererInterface $renderer */
        $renderer = $this->renderersRegistry->get($format);

        return $renderer->render($content, $variables, $renderer->isContextRequired() ? $this->contextBuilder->build($variables, $currentUser) : []);
    }
}
