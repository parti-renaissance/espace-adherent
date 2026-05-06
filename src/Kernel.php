<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    use MicroKernelTrait {
        configureContainer as protected baseConfigureContainer;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $this->baseConfigureContainer($container);

        $configDir = $this->getConfigDir();
        $container->import($configDir.'/{services}/*.php');
    }
}
