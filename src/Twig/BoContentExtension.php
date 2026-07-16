<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BoContentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('bo_html', [$this, 'renderBoContent'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Wraps back-office authored rich HTML in its styling scope.
     *
     * The wrapper is emitted here rather than written in the template so that a
     * render site cannot forget it: Tailwind Preflight flattens the paragraphs,
     * headings and lists produced by the editor, and only `.formatted-text`
     * restores them.
     */
    public function renderBoContent(?string $html): string
    {
        if (null === $html || '' === trim($html)) {
            return '';
        }

        return \sprintf('<div class="formatted-text">%s</div>', $html);
    }
}
