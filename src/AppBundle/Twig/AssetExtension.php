<?php

namespace AppBundle\Twig;

use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bridge\Twig\Extension\AssetExtension as BaseAssetExtension;

class AssetExtension extends \Twig_Extension
{
    private $router;
    private $symfonyAssetExtension;
    private $secret;
    private $env;
    private $hash;

    public function __construct(Router $router, BaseAssetExtension $symfonyAssetExtension, string $secret, string $env, string $hash)
    {
        $this->router = $router;
        $this->symfonyAssetExtension = $symfonyAssetExtension;
        $this->secret = $secret;
        $this->env = $env;
        $this->hash = $hash;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('webpack_asset', [$this, 'webpackAsset'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('static_asset', [$this, 'transformedStaticAsset'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dynamic_asset', [$this, 'transformedDynamicAsset'], ['is_safe' => ['html']]),
        );
    }

    public function transformedStaticAsset(string $path, array $parameters = [])
    {
        return $this->generateAssetUrl('static/'.$path, $parameters);
    }

    public function transformedDynamicAsset(string $path, array $parameters = [])
    {
        return $this->generateAssetUrl('images/'.$path, $parameters);
    }

    public function webpackAsset(string $path, $packageName = null)
    {
        if ($this->env === 'dev') {
            return $this->symfonyAssetExtension->getAssetUrl('built/'.$path, $packageName);
        }

        return $this->symfonyAssetExtension->getAssetUrl('built/'.$this->hash.'.'.$path, $packageName);
    }

    private function generateAssetUrl(string $path, array $parameters = [])
    {
        $parameters['fm'] = 'jpg';
        $parameters['s'] = SignatureFactory::create($this->secret)->generateSignature($path, $parameters);
        $parameters['path'] = $path;

        return $this->router->generate('asset_url', $parameters);
    }
}
