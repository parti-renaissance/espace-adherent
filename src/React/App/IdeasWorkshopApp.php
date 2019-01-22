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
            'consult' => new Route('/atelier-des-idees/consulter'),
            'contribute' => new Route('/atelier-des-idees/contribuer'),
            'propose' => new Route('/atelier-des-idees/proposer'),
            'create' => new Route('/atelier-des-idees/creer-ma-note'),
            'note' => new Route('/atelier-des-idees/note/{id}'),
            'conditions' => new Route('/atelier-des-idees/conditions-generales-utilisation'),
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
