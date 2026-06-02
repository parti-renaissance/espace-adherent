<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c): void {
    $c->extension('twig', ['strict_variables' => true]);
};
