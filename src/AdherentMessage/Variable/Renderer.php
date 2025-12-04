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
