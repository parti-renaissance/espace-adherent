<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('webpack_asset', [AssetRuntime::class, 'webpackAsset'], ['is_safe' => ['html']]),
            new TwigFunction('static_asset', [AssetRuntime::class, 'transformedStaticAsset'], ['is_safe' => ['html']]),
            new TwigFunction('media_asset', [AssetRuntime::class, 'transformedMediaAsset'], ['is_safe' => ['html']]),
        ];
    }
}
