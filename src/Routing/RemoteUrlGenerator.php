<?php

namespace AppBundle\Routing;

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

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->generator->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->generator->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->generator->generate($name, $parameters, $referenceType);
    }
}
