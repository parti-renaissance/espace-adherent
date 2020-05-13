<?php

namespace App\DependencyInjection\Compiler;

use App\Security\Firewall\ExceptionListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('security.exception_listener.main')
            ->setClass(ExceptionListener::class)
            ->addMethodCall('setApiPathPrefix', [$container->getParameter('api_path_prefix')])
        ;
    }
}
