<?php

namespace App\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class RemoteUrlGenerator implements UrlGeneratorInterface
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function generateRemoteUrl(string $name, array $parameters = []): string
    {
        return str_replace(
            'http://localhost',
            'https://en-marche.fr',
            $this->generator->generate($name, $parameters, self::ABSOLUTE_URL)
        );
    }

    public function setContext(RequestContext $context)
    {
        $this->generator->setContext($context);
    }

    public function getContext()
    {
        return $this->generator->getContext();
    }

    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->generator->generate($name, $parameters, $referenceType);
    }
}
