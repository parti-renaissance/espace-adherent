<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use Pelago\Emogrifier\CssInliner;
use Psr\Log\LoggerInterface;

class EmailCssInliner
{
    private const RESET_CSS = 'h1,h2,h3,h4,h5,h6,p,ul,ol,li{margin:0}';

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function inline(string $html): string
    {
        try {
            $inlined = CssInliner::fromHtml($html)
                ->disableStyleBlocksParsing()
                ->inlineCss(self::RESET_CSS)
                ->render();
        } catch (\Throwable $e) {
            $this->logger->warning('[SES][Campaign] CSS inlining failed, sending un-inlined HTML', ['exception' => $e]);

            return $html;
        }

        return strtr($inlined, ['%7B%7B' => '{{', '%7D%7D' => '}}']);
    }
}
