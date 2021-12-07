<?php

namespace App\DataFixtures\ORM;

use App\ChezVous\Measure\Apprentissage;
use App\ChezVous\Measure\BaisseNombreChomeurs;
use App\ChezVous\Measure\ChequeEnergie;
use App\ChezVous\Measure\ConversionSurfaceAgricoleBio;
use App\ChezVous\Measure\CouvertureFibre;
use App\ChezVous\Measure\CreationEntreprise;
use App\ChezVous\Measure\DedoublementClasses;
use App\ChezVous\Measure\DevoirsFaits;
use App\ChezVous\Measure\EmploisFrancs;
use App\ChezVous\Measure\EntreprisesAideesCovid;
use App\ChezVous\Measure\FranceRelance;
use App\ChezVous\Measure\MaisonDeSante;
use App\ChezVous\Measure\MaisonServiceAccueilPublic;
use App\ChezVous\Measure\MaPrimeRenov;
use App\ChezVous\Measure\MissionBern;
use App\ChezVous\Measure\PassCulture;
use App\ChezVous\Measure\PrimeConversionAutomobile;
use App\ChezVous\Measure\QuartierReconqueteRepublicaine;
use App\ChezVous\Measure\SuppressionTaxeHabitation;
use App\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadChezVousMeasureTypeData extends Fixture
{
    private const TYPES = [
        [
            'code' => MissionBern::TYPE,
            'label' => 'Projet de rénovation du patrimoine financé par la mission Bern',
            'sourceLink' => 'https://www.missionbern.fr/projets/',
            'sourceLabel' => 'missionbern.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=culture',
            'eligibilityLink' => null,
        ],
        [
            'code' => DedoublementClasses::TYPE,
            'label' => 'Dédoublement des classes de CP et CE1',
            'sourceLink' => 'https://data.education.gouv.fr/explore/dataset/fr-en-ecoles-ep/information/',
            'sourceLabel' => 'data.education.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=education',
            'eligibilityLink' => null,
        ],
        [
            'code' => MaisonServiceAccueilPublic::TYPE,
            'label' => 'Généralisation des maisons de service et d\'accueil au public',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/fiches-didentite-des-maisons-de-services-au-public/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=action-publique',
            'eligibilityLink' => null,
        ],
        [
            'code' => SuppressionTaxeHabitation::TYPE,
            'label' => 'Suppression de la taxe d’habitation',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/impots-locaux/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,logement',
            'eligibilityLink' => 'https://www.impots.gouv.fr/portail/simulateur-de-la-reforme-de-la-taxe-dhabitation-pour-2019',
        ],
        [
            'code' => PassCulture::TYPE,
            'label' => 'Mise en place du Pass Culture',
            'sourceLink' => 'https://www.service-public.fr/particuliers/actualites/A13201',
            'sourceLabel' => 'service-public.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=culture',
            'eligibilityLink' => 'https://pass.culture.fr/',
        ],
        [
            'code' => CreationEntreprise::TYPE,
            'label' => 'Création nettes d\'entreprises',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/base-sirene-des-entreprises-et-de-leurs-etablissements-siren-siret/',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises',
            'eligibilityLink' => null,
        ],
        [
            'code' => BaisseNombreChomeurs::TYPE,
            'label' => 'Baisse du nombre de chômeurs',
            'sourceLink' => 'https://statistiques.pole-emploi.org/stmt/trsl?fa=M&lb=0',
            'sourceLabel' => 'pole-emploi.org',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises,industrie,apprentissage,dialogue-social',
            'eligibilityLink' => null,
        ],
        [
            'code' => EmploisFrancs::TYPE,
            'label' => 'Emplois francs',
            'sourceLink' => 'https://travail-emploi.gouv.fr/emploi/emplois-francs/',
            'sourceLabel' => 'travail-emploi.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises,industrie,apprentissage,dialogue-social,territoires',
            'eligibilityLink' => 'https://travail-emploi.gouv.fr/emploi/emplois-francs/',
        ],
        [
            'code' => CouvertureFibre::TYPE,
            'label' => 'Couverture en fibre de tout le territoire',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/le-marche-du-haut-et-tres-haut-debit-fixe-deploiements/#resource-b8d4f328-0dd4-40a7-9b68-b2a2266abddb',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=numerique',
            'eligibilityLink' => null,
        ],
        [
            'code' => PrimeConversionAutomobile::TYPE,
            'label' => 'Prime à la conversion automobile',
            'sourceLink' => 'https://www.primealaconversion.gouv.fr/dboneco/accueil/',
            'sourceLabel' => 'primealaconversion.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => 'https://www.primealaconversion.gouv.fr/dboneco/accueil/',
        ],
        [
            'code' => ChequeEnergie::TYPE,
            'label' => 'Chèque énergie',
            'sourceLink' => 'https://chequeenergie.gouv.fr/',
            'sourceLabel' => 'chequeenergie.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => 'https://chequeenergie.gouv.fr/',
        ],
        [
            'code' => ConversionSurfaceAgricoleBio::TYPE,
            'label' => 'Conversion de la surface agricole en bio',
            'sourceLink' => 'https://www.agencebio.org/vos-outils/les-chiffres-cles/',
            'sourceLabel' => 'agencebio.org',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => null,
        ],
        [
            'code' => QuartierReconqueteRepublicaine::TYPE,
            'label' => 'Création d\'un quartier de reconquête républicaine',
            'sourceLink' => 'https://www.interieur.gouv.fr/Espace-presse/Dossiers-de-presse/Un-an-de-la-police-de-securite-du-quotidien',
            'sourceLabel' => 'interieur.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=securite',
            'eligibilityLink' => null,
        ],

        [
            'code' => FranceRelance::TYPE,
            'label' => 'France Relance',
            'sourceLink' => 'https://datavision.economie.gouv.fr/relance-industrie',
            'sourceLabel' => 'France Relance',
            'oldolfLink' => 'https://transformer.en-marche.fr',
            'eligibilityLink' => null,
        ],
        [
            'code' => DevoirsFaits::TYPE,
            'label' => 'Devoirs Faits',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/barometre-des-resultats-de-laction-publique',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=education',
            'eligibilityLink' => null,
        ],
        [
            'code' => MaisonDeSante::TYPE,
            'label' => 'Maison de santé',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/barometre-des-resultats-de-laction-publique',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr',
            'eligibilityLink' => null,
        ],
        [
            'code' => MaPrimeRenov::TYPE,
            'label' => 'MaPrimeRenov',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/barometre-des-resultats-de-laction-publique',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=ecologie',
            'eligibilityLink' => null,
        ],
        [
            'code' => Apprentissage::TYPE,
            'label' => 'Apprentissage',
            'sourceLink' => 'https://www.data.gouv.fr/fr/datasets/barometre-des-resultats-de-laction-publique',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr',
            'eligibilityLink' => null,
        ],
        [
            'code' => EntreprisesAideesCovid::TYPE,
            'label' => 'Entreprises aidées covid',
            'sourceLink' => 'https://github.com/etalab/dashboard-aides-entreprises/tree/master/published-data/activite-partielle',
            'sourceLabel' => 'data.gouv.fr',
            'oldolfLink' => 'https://transformer.en-marche.fr',
            'eligibilityLink' => null,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::TYPES as $type) {
            $measureType = new MeasureType($type['code'], $type['label']);
            $measureType->setSourceLabel($type['sourceLabel']);
            $measureType->setSourceLink($type['sourceLink']);
            $measureType->setOldolfLink($type['oldolfLink']);
            $measureType->setEligibilityLink($type['eligibilityLink']);

            $manager->persist($measureType);
            $this->setReference(sprintf('chez-vous-measure-type-%s', $type['code']), $measureType);
        }

        $manager->flush();
    }
}
