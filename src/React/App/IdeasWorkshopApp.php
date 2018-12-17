<?php

namespace AppBundle\React\App;

use AppBundle\React\PageMetaData;
use AppBundle\React\PageMetaDataInterface;
use AppBundle\React\ReactAppInterface;
use Symfony\Component\Routing\Route;

class IdeasWorkshopApp implements ReactAppInterface
{
    public function getDirectory(): string
    {
        return 'atelier-des-idees';
    }

    public function enableInProduction(): bool
    {
        // ;)
        return false;
    }

    public function getRoutes(): array
    {
        return [
            'home' => new Route('/atelier-des-idees'),
            'search' => new Route('/atelier-des-idees/recherche'),
            'example2' => new Route('/atelier-des-idees/exemple2'),
        ];
    }

    public function getPageMetaData(): PageMetaDataInterface
    {
        return new PageMetaData(
            'L\'atelier des idées',
            'Description à définir',
            1698,
            550
        );
    }
}
