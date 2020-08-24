<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

class JobEnum extends Enum
{
    public const JOBS = [
        'Agents administratifs',
        'Agents commerciaux',
        'Agents de service, de sécurité, d\'accueil ou de surveillance',
        'Agriculteurs, pêcheurs, horticulteurs, éleveurs, chasseurs',
        'Artisans',
        'Auxiliaires médicaux et ambulanciers (aides soignants, opticiens, infirmiers, etc.)',
        'Cadres administratifs',
        'Cadres commerciaux',
        'Cadres techniques',
        'Chargés de clientèle',
        'Chargés de mission',
        'Chefs d\'entreprise de 10 à 49 salariés',
        'Chefs d\'entreprise de 50 à 499 salariés',
        'Chefs d\'entreprise de 500 salariés et plus',
        'Chefs d\'équipe 10 à 49 salariés',
        'Chefs d\'équipe de 50 à 499 salariés',
        'Chefs d\'équipe de 500 salariés et plus',
        'Chefs de produits',
        'Chefs de projets',
        'Chercheurs, chargés de recherche ou d\'études',
        'Clergé, religieux',
        'Commerçants',
        'Conseillers',
        'Directeurs de structures de 10 à 49 salariés',
        'Directeurs de structures de 50 à 499 salariés',
        'Directeurs de structures de plus de 500 salariés',
        'Employés',
        'Enseignants, professeurs, instituteurs, inspecteurs',
        'Entrepreneurs',
        'Experts comptables, comptables agréés, libéraux',
        'Exploitants',
        'Formateurs, animateurs, éducateurs, moniteurs',
        'Gestionnaires d\'établissements privés (enseignement, santé, social)',
        'Ingénieurs',
        'Métiers artistiques et créatifs (photographes, auteurs, etc.)',
        'Métiers de l\'aménagement et de l\'urbanisme (géomètres, architectes, etc.)',
        'Métiers du bâtiment et des travaux publics (maçons, électriciens, couvreurs, plombiers, chauffagistes, peintres, etc.)',
        'Métiers du journalisme et de la communication',
        'Ouvriers',
        'Personnes exerçant un mandat politique ou syndical',
        'Pharmaciens, préparateurs en pharmacie',
        'Policiers et militaires',
        'Professions intermédiaires techniques et commerciales',
        'Professions juridiques (avocats, magistrats, notaires, huissiers de justice, etc.)',
        'Professions médicales (médecins, sages-femmes, chirurgiens-dentistes)',
        'Psychologues, psychanalystes, psychothérapeutes',
        'Techniciens',
        'Vétérinaires',
    ];
}
