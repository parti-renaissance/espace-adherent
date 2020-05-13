<?php

namespace App\React\App;

use App\React\PageMetaData;
use App\React\PageMetaDataInterface;
use App\React\ReactAppInterface;
use Symfony\Component\Routing\Route;

class CitizenProjectApp implements ReactAppInterface
{
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
            'discover' => new Route('/projets-citoyens/cle-en-main'),
            'search' => new Route('/projets-citoyens/recherche'),
        ];
    }

    public function getPageMetaData(): PageMetaDataInterface
    {
        return new PageMetaData(
            'Les projets citoyens',
            'Les projets citoyens initiés par La République En Marche ! sont des actions locales qui permettent d\'améliorer concrètement le quotidien des habitants d\'un quartier, d\'un village, en réunissant la force et les compétences de tous ceux qui veulent agir.',
            2501,
            1313,
            'https://storage.googleapis.com/en-marche-prod/images/cp_sharing.png'
        );
    }
}
