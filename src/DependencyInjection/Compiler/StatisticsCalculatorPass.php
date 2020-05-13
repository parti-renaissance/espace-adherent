<?php

namespace App\DependencyInjection\Compiler;

use App\Statistics\Acquisition\Aggregator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StatisticsCalculatorPass implements CompilerPassInterface
{
    private const DEFAULT_PRIORITY = 256;

    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(Aggregator::class);

        foreach ($container->findTaggedServiceIds('app.acquisition_statistics.calculator') as $id => $tags) {
            $tag = current($tags);
            $priority = $tag['priority'] ?? self::DEFAULT_PRIORITY;
            $definition->addMethodCall('addCalculator', [new Reference($id), $priority]);
        }
    }
}
