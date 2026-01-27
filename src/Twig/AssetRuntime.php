<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Media;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bridge\Twig\Extension\AssetExtension as BaseAssetExtension;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AssetRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly BaseAssetExtension $symfonyAssetExtension,
        private readonly string $secret,
        private readonly string $appVersion,
        private readonly MimeTypesInterface $mimeTypes,
    ) {
    }

    public function transformedStaticAsset(
        string $path,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        ?string $appDomain = null,
    ): string {
        $parameters['cache'] = $this->appVersion;

        return $this->generateAssetUrl('static/'.$path, $parameters, $referenceType, $appDomain);
    }

    public function transformedMediaAsset(
        Media $media,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        $parameters['cache'] = substr(md5($media->getUpdatedAt()->format('U')), 0, 20);

        if ($media->isVideo()) {
            $parameters = [];
            $parameters['path'] = $media->getPathWithDirectory();
            $parameters['mime_type'] = $media->getMimeType();
            $parameters['is_video'] = true;

            return $this->urlGenerator->generate('asset_url', $parameters, $referenceType);
        }
        // No compression and no resizing if no compressed display
        if (!$media->isCompressedDisplay()) {
            unset($parameters['q'], $parameters['w']);
        }

        $appDomain = null;
        if (!empty($parameters['app_domain'])) {
            $appDomain = $parameters['app_domain'];
            unset($parameters['app_domain']);
        }

        return $this->generateAssetUrl($media->getPathWithDirectory(), $parameters, $referenceType, $appDomain);
    }

    public function webpackAsset(string $path, $packageName = null): string
    {
        return $this->symfonyAssetExtension->getAssetUrl($path, $packageName);
    }

    public function getAssetMimeType(string $path): string
    {
        return $this->mimeTypes->getMimeTypes(pathinfo($path, \PATHINFO_EXTENSION))[0];
    }

    private function generateAssetUrl(
        string $path,
        array $parameters,
        int $referenceType,
        ?string $appDomain = null,
    ): string {
        $parameters['fm'] = 'pjpg';

        if (str_ends_with($path, 'gif')) {
            $parameters['fm'] = 'gif';

            $parameters = array_intersect_key($parameters, array_fill_keys(['fm', 'cache'], true));
        }

        $parameters['s'] = SignatureFactory::create($this->secret)->generateSignature($path, $parameters);
        $parameters['path'] = $path;

        if ($appDomain) {
            $parameters['app_domain'] = $appDomain;
        }

        return $this->urlGenerator->generate('asset_url', $parameters, $referenceType);
    }
}
