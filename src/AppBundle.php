<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\SecurityPass;
use AppBundle\DependencyInjection\Compiler\StatisticsCalculatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new StatisticsCalculatorPass())
            ->addCompilerPass(new MessengerPass())
            ->addCompilerPass(new SecurityPass())
        ;
    }
}
