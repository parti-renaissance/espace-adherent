<?php

namespace App\Storage;

use League\Flysystem\Adapter\Local;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebLocalAdapter extends Local implements UrlAdapterInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getUrl($filePath)
    {
        return $this->urlGenerator->generate('asset_url', ['path' => $filePath], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
