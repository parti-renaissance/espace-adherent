<?php

namespace App\Twig;

use App\Entity\Media;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bridge\Twig\Extension\AssetExtension as BaseAssetExtension;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AssetRuntime
{
    private $router;
    private $symfonyAssetExtension;
    private $secret;
    private $env;
    private $hash;

    public function __construct(
        Router $router,
        BaseAssetExtension $symfonyAssetExtension,
        string $secret,
        string $env,
        ?string $hash
    ) {
        $this->router = $router;
        $this->symfonyAssetExtension = $symfonyAssetExtension;
        $this->secret = $secret;
        $this->env = $env;
        $this->hash = $hash;

        if (false === strpos($env, 'dev') && !$this->hash) {
            throw new \RuntimeException('The "assets_hash" parameter is mandatory for all environments except dev. Please build them.');
        }
    }

    public function transformedStaticAsset(
        string $path,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $parameters['cache'] = $this->hash;

        return $this->generateAssetUrl('static/'.$path, $parameters, $referenceType);
    }

    public function transformedMediaAsset(
        Media $media,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $parameters['cache'] = substr(md5($media->getUpdatedAt()->format('U')), 0, 20);

        if ($media->isVideo()) {
            $parameters = [];
            $parameters['path'] = $media->getPathWithDirectory();
            $parameters['mime_type'] = $media->getMimeType();
            $parameters['is_video'] = true;

            return $this->router->generate('asset_url', $parameters, $referenceType);
        } else {
            // No compression and no resizing if no compressed display
            if (!$media->isCompressedDisplay()) {
                unset($parameters['q'], $parameters['w']);
            }

            return $this->generateAssetUrl($media->getPathWithDirectory(), $parameters, $referenceType);
        }
    }

    public function webpackAsset(string $path, $packageName = null): string
    {
        if (false !== strpos($this->env, 'dev')) {
            return $this->symfonyAssetExtension->getAssetUrl('built/'.$path, $packageName);
        }

        return $this->symfonyAssetExtension->getAssetUrl('built/'.$this->hash.'.'.$path, $packageName);
    }

    private function generateAssetUrl(string $path, array $parameters, int $referenceType): string
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
