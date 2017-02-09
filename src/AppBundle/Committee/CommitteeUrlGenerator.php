<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeUrlGenerator
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getPath(string $routeName, Committee $committee, array $params = []): string
    {
        return $this->generateUrl($routeName, $committee, $params);
    }

    public function getUrl(string $routeName, Committee $committee, array $params = []): string
    {
        return $this->generateUrl($routeName, $committee, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateUrl(string $routeName, Committee $committee, array $params = [], int $type = UrlGenerator::ABSOLUTE_PATH): string
    {
        $params['uuid'] = (string) $committee->getUuid();
        $params['slug'] = $committee->getSlug();

        return $this->urlGenerator->generate($routeName, $params, $type);
    }
}
