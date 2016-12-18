<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Twig\Extension\AssetExtension;

class WebpackAssetExtension extends \Twig_Extension
{
    private $symfonyAssetExtension;
    private $env;
    private $hash;

    public function __construct(AssetExtension $symfonyAssetExtension, string $env, string $hash)
    {
        $this->symfonyAssetExtension = $symfonyAssetExtension;
        $this->env = $env;
        $this->hash = $hash;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('webpack_asset', [ $this, 'webpackAsset' ]),
        );
    }

    public function webpackAsset(string $path, $packageName = null)
    {
        if ($this->env === 'dev') {
            return $this->symfonyAssetExtension->getAssetUrl('built/'.$path, $packageName);
        }

        return $this->symfonyAssetExtension->getAssetUrl('built/'.$this->hash.'.'.$path, $packageName);
    }

    public function getName()
    {
        return 'app_webpack_asset';
    }
}
