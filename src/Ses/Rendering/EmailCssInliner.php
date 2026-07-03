<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use Pelago\Emogrifier\CssInliner;
use Psr\Log\LoggerInterface;

class EmailCssInliner
{
    private const RESET_CSS = 'h1,h2,h3,h4,h5,h6,p,ul,ol,li{margin:0}';

    private const CONTENT_CSS = <<<'CSS'
        .content{font-size:16px;line-height:1.6;color:#2d353c}
        .content h1{font-size:22px;font-weight:600;line-height:1.5;color:#1d1d1f;margin:0;letter-spacing:-0.02em}
        .content h2{font-size:20px;font-weight:600;line-height:1.5;color:#1d1d1f;margin:0;letter-spacing:-0.01em}
        .content h3{font-size:19px;font-weight:600;line-height:1.5;color:#1d1d1f;margin:0;letter-spacing:-0.01em}
        .content h4{font-size:18px;font-weight:600;line-height:1.6;color:#1d1d1f;margin:0}
        .content h5{font-size:17px;font-weight:600;line-height:1.6;color:#1d1d1f;margin:0}
        .content h6{font-size:16px;font-weight:600;line-height:1.6;color:#1d1d1f;margin:0}
        .content p{font-size:16px;font-weight:400;line-height:1.6;color:#424245;margin:0;letter-spacing:0.01em}
        .content ul,.content ol{font-size:16px;font-weight:400;line-height:1.6;color:#424245;margin:0;padding-left:24px}
        .content ul{list-style-type:disc}
        .content ol{list-style-type:decimal}
        .content li{font-size:16px;font-weight:400;line-height:1.6;color:#424245;margin:0;letter-spacing:0.01em}
        .content strong,.content b{font-weight:600;color:#1d1d1f}
        .content em,.content i{font-style:italic}
        .content a{color:#4291E1;text-decoration:underline;font-weight:500}
        .padding-responsive{padding-left:40px !important;padding-right:40px !important}
        .padding-responsive-top{padding-top:40px !important}
        .spacing-responsive{height:64px !important}
        @media only screen and (max-width:600px){.content{font-size:16px !important}}
        @media only screen and (max-width:600px){.padding-responsive{padding-left:24px !important;padding-right:24px !important}}
        @media only screen and (max-width:600px){.padding-responsive-top{padding-top:24px !important}}
        @media only screen and (max-width:600px){.spacing-responsive{height:40px !important}}
        CSS;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function inline(string $html): string
    {
        try {
            $inlined = CssInliner::fromHtml($html)
                ->disableStyleBlocksParsing()
                ->inlineCss(self::RESET_CSS."\n".self::CONTENT_CSS)
                ->render();
        } catch (\Throwable $e) {
            $this->logger->warning('[SES][Campaign] CSS inlining failed, sending un-inlined HTML', ['exception' => $e]);

            return $html;
        }

        return strtr($inlined, ['%7B%7B' => '{{', '%7D%7D' => '}}']);
    }
}
