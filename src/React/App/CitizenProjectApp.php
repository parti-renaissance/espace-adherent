<?php

namespace AppBundle\React\App;

use AppBundle\React\ReactAppInterface;
use Symfony\Component\Routing\Route;

class CitizenProjectApp implements ReactAppInterface
{
    public function getTitle(): string
    {
        return 'Projets citoyens';
    }

    public function getDirectory(): string
    {
        return 'projets-citoyens';
    }

    public function enableInProduction(): bool
    {
        return true;
    }

    public function getRoutes(): array
    {
        return [
            'home' => new Route('/projets-citoyens'),
            'discover' => new Route('/projets-citoyens/decouvrir'),
            'search' => new Route('/projets-citoyens/recherche'),
        ];
    }
}
