<?php

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use Psr\Container\ContainerInterface;

class DIContext implements Context
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function getParameter(string $id)
    {
        return $this->container->getParameter($id);
    }
}
