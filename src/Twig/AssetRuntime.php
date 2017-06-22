<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Media;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bridge\Twig\Extension\AssetExtension as BaseAssetExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AssetRuntime
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

    public function transformedStaticAsset(string $path, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['cache'] = $this->hash;

        return $this->generateAssetUrl('static/'.$path, $parameters, $referenceType);
    }

    public function transformedMediaAsset(Media $media, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['cache'] = substr(md5($media->getUpdatedAt()->format('U')), 0, 20);

        return $this->generateAssetUrl('images/'.$media->getPath(), $parameters, $referenceType);
    }

    public function webpackAsset(string $path, $packageName = null): string
    {
        if ($this->env === 'dev') {
            return $this->symfonyAssetExtension->getAssetUrl('built/'.$path, $packageName);
        }

        return $this->symfonyAssetExtension->getAssetUrl('built/'.$this->hash.'.'.$path, $packageName);
    }

    private function generateAssetUrl(string $path, array $parameters = [], int $referenceType): string
    {
        $parameters['fm'] = 'pjpg';

        if ('gif' === substr($path, -3)) {
            $parameters['fm'] = 'gif';

            $parameters = array_intersect_key($parameters, array_fill_keys(['fm', 'cache'], true));
        }

        $parameters['s'] = SignatureFactory::create($this->secret)->generateSignature($path, $parameters);
        $parameters['path'] = $path;

        return $this->router->generate('asset_url', $parameters, $referenceType);
    }
}
