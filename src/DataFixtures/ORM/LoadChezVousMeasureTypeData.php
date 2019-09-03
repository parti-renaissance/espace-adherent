<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ChezVous\MeasureType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadChezVousMeasureTypeData extends AbstractFixture
{
    private const TYPES = [
        [
            'code' => 'dedoublement_classes',
            'label' => 'Dédoublement des classes de CP et CE1',
            'sourceLink' => 'https://data.education.gouv.fr/explore/dataset/fr-en-ecoles-ep/information/',
            'sourceLabel' => 'data.education.gouv.fr',
        ],
        [
            'code' => 'maison_service_accueil_public',
            'label' => 'Généralisation des maisons de service et d\'accueil au public',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/fiches-didentite-des-maisons-de-services-au-public/',
            'sourceLabel' => 'data.gouv.fr',
        ],
        [
            'code' => 'suppression_taxe_habitation',
            'label' => 'Suppression de la taxe d’habitation',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/impots-locaux/',
            'sourceLabel' => 'data.gouv.fr',
        ],
        [
            'code' => 'pass_culture',
            'label' => 'Mise en place du Pass Culture',
            'sourceLink' => 'https://www.service-public.fr/particuliers/actualites/A13201',
            'sourceLabel' => 'service-public.fr',
        ],
        [
            'code' => 'creation_entreprises',
            'label' => 'Création nettes d\'entreprises',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/base-sirene-des-entreprises-et-de-leurs-etablissements-siren-siret/',
            'sourceLabel' => 'data.gouv.fr',
        ],
        [
            'code' => 'baisse_nombre_chomeurs',
            'label' => 'Baisse du nombre de chômeurs',
            'sourceLink' => 'https://statistiques.pole-emploi.org/stmt/trsl?fa=M&lb=0',
            'sourceLabel' => 'pole-emploi.org',
        ],
        [
            'code' => 'emplois_francs',
            'label' => 'Emplois francs',
            'sourceLink' => 'https://travail-emploi.gouv.fr/emploi/emplois-francs/',
            'sourceLabel' => 'travail-emploi.gouv.fr',
        ],
        [
            'code' => 'couverture_fibre',
            'label' => 'Couverture en fibre de tout le territoire',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/le-marche-du-haut-et-tres-haut-debit-fixe-deploiements/#resource-b8d4f328-0dd4-40a7-9b68-b2a2266abddb',
            'sourceLabel' => 'data.gouv.fr',
        ],
        [
            'code' => 'prime_conversion_automobile',
            'label' => 'Prime à la conversion automobile',
            'sourceLink' => 'https://www.primealaconversion.gouv.fr/dboneco/accueil/',
            'sourceLabel' => 'primealaconversion.gouv.fr',
        ],
        [
            'code' => 'cheque_energie',
            'label' => 'Chèque énergie',
            'sourceLink' => 'https://chequeenergie.gouv.fr/',
            'sourceLabel' => 'chequeenergie.gouv.fr',
        ],
        [
            'code' => 'conversion_surface_agricole_bio',
            'label' => 'Conversion de la surface agricole en bio',
            'sourceLink' => 'https://www.agencebio.org/vos-outils/les-chiffres-cles/',
            'sourceLabel' => 'agencebio.org',
        ],
        [
            'code' => 'quartier_reconquete_republicaine',
            'label' => 'Création d\'un quartier de reconquête républicaine',
            'sourceLink' => 'https://www.interieur.gouv.fr/Espace-presse/Dossiers-de-presse/Un-an-de-la-police-de-securite-du-quotidien',
            'sourceLabel' => 'interieur.gouv.fr',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::TYPES as $type) {
            $measureType = new MeasureType(
                $type['code'],
                $type['label'],
                $type['sourceLink'],
                $type['sourceLabel']
            );

            $manager->persist($measureType);
            $this->setReference(sprintf('chez-vous-measure-type-%s', $type['code']), $measureType);
        }

        $manager->flush();
    }
}
