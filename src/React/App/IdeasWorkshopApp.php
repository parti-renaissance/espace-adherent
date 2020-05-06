<?php

namespace App\React\App;

use App\React\PageMetaData;
use App\React\PageMetaDataInterface;
use App\React\ReactAppInterface;
use Symfony\Component\Routing\Route;

class IdeasWorkshopApp implements ReactAppInterface
{
    public function getDirectory(): string
    {
        return 'atelier-des-idees';
    }

    public function enableInProduction(): bool
    {
        return true;
    }

    public function getRoutes(): array
    {
        return [
            'home' => new Route('/atelier-des-idees'),
            'support' => new Route('/atelier-des-idees/soutenir'),
            'contribute' => new Route('/atelier-des-idees/contribuer'),
            'propose' => new Route('/atelier-des-idees/proposer'),
            'create' => new Route('/atelier-des-idees/creer-ma-proposition'),
            'proposition' => new Route('/atelier-des-idees/proposition/{id}'),
            'conditions' => new Route('/atelier-des-idees/conditions-generales-utilisation'),
        ];
    }

    public function getPageMetaData(): PageMetaDataInterface
    {
        return new PageMetaData(
            'L\'atelier des idées',
            'Vous avez envie de contribuer à la réflexion de La République En Marche ? De proposer vos idées ? Avec l\'Atelier des idées c\'est possible !',
            1200,
            630,
            'https://storage.googleapis.com/en-marche-fr/icons/Mailjet/atelier_des_idees_metadata.png'
        );
    }
}
