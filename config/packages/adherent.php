<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('adherent_interests', [
        'culture' => 'Culture',
        'democratie' => 'Démocratie',
        'economie' => 'Économie',
        'education' => 'Éducation',
        'jeunesse' => 'Jeunesse',
        'egalite' => 'Égalité F/H',
        'europe' => 'Europe',
        'inclusion' => 'Inclusion',
        'international' => 'International',
        'justice' => 'Justice',
        'lgbt' => 'LGBT+',
        'numerique' => 'Numérique',
        'puissance_publique' => 'Puissance publique',
        'republique' => 'République',
        'ruralite' => 'Ruralité',
        'sante' => 'Santé',
        'securite_et_defense' => 'Sécurité et Défense',
        'solidarites' => 'Solidarités',
        'sport' => 'Sport',
        'transition_ecologique' => 'Transition écologique',
        'travail' => 'Travail',
        'villes_et_quartiers' => 'Villes et quartiers',
        'famille' => 'Famille',
    ]);
};
