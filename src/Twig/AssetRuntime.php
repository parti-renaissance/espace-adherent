<?php

namespace App\Twig;

use App\Entity\Media;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bridge\Twig\Extension\AssetExtension as BaseAssetExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AssetRuntime implements RuntimeExtensionInterface
{
    private $urlGenerator;
    private $symfonyAssetExtension;
    private $secret;
    private $hash;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        BaseAssetExtension $symfonyAssetExtension,
        string $secret,
        ?string $hash
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->symfonyAssetExtension = $symfonyAssetExtension;
        $this->secret = $secret;
        $this->hash = $hash;
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

            return $this->urlGenerator->generate('asset_url', $parameters, $referenceType);
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
        return $this->symfonyAssetExtension->getAssetUrl($path, $packageName);
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

        return $this->urlGenerator->generate('asset_url', $parameters, $referenceType);
    }
}
