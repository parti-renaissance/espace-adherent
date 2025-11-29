<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DIContext implements Context
{
    public function __construct(private readonly ContainerInterface $driverContainer)
    {
    }

    public function get(string $id): ?object
    {
        return $this->driverContainer->get($id);
    }

    public function getParameter(string $id)
    {
        return $this->driverContainer->getParameter($id);
    }
}
