<?php

namespace AppBundle\React;

use Symfony\Component\Routing\Route;

interface ReactAppInterface
{
    /**
     * Title to use in the <title> HTML tag.
     */
    public function getTitle(): string;

    /**
     * Return the directory name of this app.
     */
    public function getDirectory(): string;

    /**
     * Whether this app should be only available in canary mode or not.
     */
    public function enableInProduction(): bool;

    /**
     * Return a list of routes (in the form name => Route) to associate to this app.
     *
     * @return Route[]
     */
    public function getRoutes(): array;
}
