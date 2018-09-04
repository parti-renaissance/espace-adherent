<?php

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\Statistics\Acquisition\Aggregator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StatisticsCalculatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(Aggregator::class);

        foreach ($container->findTaggedServiceIds('app.acquisition_statistics.calculator') as $id => $tags) {
            $definition->addMethodCall('addCalculator', [new Reference($id)]);
        }
    }
}
