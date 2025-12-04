<?php

declare(strict_types=1);

namespace Tests\App\Behat\Extension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tests\App\Behat\HttpCall\HttpCallListener;

class AppExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'app';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
        // Override Behatch's HttpCallListener with our version
        // that guards against unstable Selenium sessions (isStarted check)
        $definition = $container->getDefinition('behatch.http_call.listener');
        $definition->setClass(HttpCallListener::class);
        $definition->setArguments([
            new Reference('behatch.context_supported.voter'),
            new Reference('behatch.http_call.result_pool'),
            new Reference('mink'),
        ]);
    }
}
