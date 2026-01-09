<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\NationalEvent\NationalEvent;
use App\Entity\UploadableFile;
use App\Form\NationalEvent\PackageField\PlaceChoiceFieldFormType;
use App\Form\NationalEvent\PackageField\SelectFieldFormType;
use App\NationalEvent\NationalEventTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadNationalEventData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($event = new NationalEvent());

        $event->setName('Event national 1');
        $event->alertEnabled = true;
        $event->alertTitle = 'Venez nombreux !';
        $event->startDate = new \DateTime('+1 month');
        $event->endDate = new \DateTime()->add(new \DateInterval('P1M2D'));
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');
        $event->textIntro = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textHelp = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->source = '123xyz';
        $this->setReference('event-national-1', $event);

        $manager->persist($event = new NationalEvent());
        $event->setName('Event national 2');
        $event->startDate = new \DateTime('+1.1 month');
        $event->endDate = new \DateTime()->add(new \DateInterval('P1M2D'));
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');
        $event->textIntro = '<p>Voici un nouvel event</p>';
        $event->textHelp = '<p>Il suffit de remplir le formulaire</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->source = '123xyz';
        $this->setReference('event-national-2', $event);

        $manager->persist($event = new NationalEvent());

        $event->setName('Campus');
        $event->alertEnabled = true;
        $event->type = NationalEventTypeEnum::CAMPUS;
        $event->alertTitle = 'Venez nombreux !';
        $event->inscriptionEditDeadline = new \DateTime('+1 month');
        $event->startDate = new \DateTime('-1 hour');
        $event->endDate = new \DateTime()->add(new \DateInterval('P1M2D'));
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');
        $event->textIntro = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textHelp = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->intoImage = new UploadableFile();
        $event->intoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/campus/campus-illustration.png', 'campus-illustration.png', 'image/png', null, true));

        $event->ogImage = new UploadableFile();
        $event->ogImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/campus/campus-illustration.png', 'campus-illustration.png', 'image/png', null, true));

        $event->alertLogoImage = new UploadableFile();
        $event->alertLogoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/campus/campus-illustration.png', 'campus-illustration.png', 'image/png', null, true));

        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            null,
            null,
            'EEEE d MMMM'
        );

        $startDate = $formatter->format($event->startDate);
        $endDate = $formatter->format($event->endDate);

        $event->packageConfig = [
            [
                'cle' => 'visitDay',
                'titre' => 'Votre présence au Campus',
                'options' => [
                    [
                        'id' => 'weekend',
                        'titre' => "Le {$startDate} et le {$endDate}",
                        'description' => 'L’hébergement reste à la charge des participants que nous vous invitons à réserver de votre côté.',
                    ],
                    [
                        'id' => 'dimanche',
                        'titre' => "Seulement le {$endDate}",
                        'description' => 'L’essentiel du Campus se déroule sur la deuxième journée.',
                    ],
                ],
            ],
            [
                'cle' => 'transport',
                'titre' => 'Transport depuis Paris vers Arras et retour',
                'options' => [
                    [
                        'id' => 'dimanche_train',
                        'dependence' => ['dimanche'],
                        'recap_label' => 'Train aller-retour',
                        'quota' => 1000,
                        'titre' => 'Train (Paris >< Arras) Dimanche',
                        'montant' => 50,
                        'description' => 'Départ 7h45 à Paris gare du nord<br/>Retour à 17h45 à Paris gare du nord',
                    ],
                    [
                        'id' => 'dimanche_bus',
                        'dependence' => ['dimanche'],
                        'recap_label' => 'Bus aller-retour',
                        'quota' => 600,
                        'titre' => 'Bus (Paris >< Arras) Dimanche',
                        'montant' => 20,
                        'description' => 'Départ 7h45 à Paris gare du nord<br/>Retour à 17h45 à Paris gare du nord',
                    ],
                    [
                        'id' => 'train_aller',
                        'dependence' => ['dimanche'],
                        'recap_label' => 'Train aller uniquement',
                        'quota' => 200,
                        'titre' => 'Train (Paris > Arras) Dimanche',
                        'montant' => 25,
                        'description' => 'Départ 7h45 à Paris gare du nord',
                    ],
                    [
                        'id' => 'gratuit',
                        'dependence' => ['weekend', 'dimanche'],
                        'titre' => 'Je viens par mes propres moyens',
                    ],
                ],
            ],
            [
                'cle' => 'accommodation',
                'titre' => 'Hébergement pour la nuit du samedi au dimanche',
                'dependence' => ['dimanche'],
                'options' => [
                    [
                        'id' => 'chambre_individuelle',
                        'recap_label' => 'Chambre individuelle',
                        'titre' => 'Chambre individuelle',
                        'montant' => 49,
                        'quota' => 50,
                        'accompagnement' => true,
                        'pid_label' => 'Vous souhaitez partager votre chambre en couple ?',
                        'pid_description' => '<div>Si vous souhaitez partager votre chambre avec votre compagne ou votre compagnon, renseignez ici son numéro adhérent.</div>',
                        'description' => 'Je réserve une chambre individuelle',
                    ],
                    [
                        'id' => 'chambre_partagee',
                        'recap_label' => 'Chambre partagée (à deux)',
                        'titre' => 'Chambre partagée (à deux)',
                        'montant' => 49,
                        'quota' => 50,
                        'accompagnement' => true,
                        'pid_label' => 'Souhaitez-vous flécher votre partenaire de chambre ?',
                        'pid_description' => '<div>Vous pouvez indiquer ici le numéro adhérent ou code invitation d\'un autre participant avec qui vous souhaitez partager la chambre.<br/><br/>Par défaut, nous choisirons un participant de même civilité et d\'âge similaire.</div>',
                        'description' => 'Je réserve une chambre partagée',
                    ],
                    [
                        'id' => 'gratuit',
                        'titre' => 'Je n\'ai pas besoin d\'hébergement',
                        'description' => 'Je trouve un hébergement par mes propres moyens',
                    ],
                ],
            ],
        ];

        $this->setReference('event-national-3', $event);

        $manager->persist($event = new NationalEvent());
        $event->setName('Event passé');
        $event->startDate = new \DateTime('-10 days');
        $event->endDate = new \DateTime('-8 days');
        $event->ticketStartDate = new \DateTime('-11 days');
        $event->ticketEndDate = new \DateTime('-10 days');
        $event->textIntro = '<p>Voici un event passé</p>';
        $event->textHelp = '<p>Il suffit de remplir le formulaire</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $this->setReference('event-national-4', $event);

        $manager->persist($event = new NationalEvent());

        $event->setName('Event JEM');
        $event->type = NationalEventTypeEnum::JEM;
        $event->inscriptionEditDeadline = new \DateTime('+1 month');
        $event->startDate = new \DateTime('-1.5 hour');
        $event->endDate = new \DateTime()->add(new \DateInterval('P1M2D'));
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');
        $event->textIntro = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textHelp = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->intoImage = new UploadableFile();
        $event->intoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../app/data/static/meeting-jem.png', 'meeting-jem.png', 'image/png', null, true));

        $event->ogImage = new UploadableFile();
        $event->ogImage->setUploadFile(new UploadedFile(__DIR__.'/../../../app/data/static/meeting-jem.png', 'meeting-jem.png', 'image/png', null, true));

        $event->alertLogoImage = new UploadableFile();
        $event->alertLogoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../app/data/static/jem-logo.png', 'jem-logo.png', 'image/png', null, true));

        $event->logoImage = new UploadableFile();
        $event->logoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../app/data/static/jem-logo.png', 'jem-logo.png', 'image/png', null, true));

        $event->discountLabel = 'Je souhaite bénéficier du fond de solidarité';
        $event->discountHelp = '<p class="text-xs">Fidèles à nos valeurs d’égalité réelle et d’émancipation, nous avons mis en place un fonds de solidarité afin de permettre à toutes et tous de participer à l’événement, sans que la question financière ne constitue un frein.<br/><br/>Toute personne souhaitant en bénéficier peut en faire la demande en toute confidentialité auprès de Charles Léron (<a href="mailto:charles.leron@lesjem.fr" class="underline">charles.leron@lesjem.fr</a>).</p>';

        $event->packageConfig = [
            [
                'cle' => 'packagePlan',
                'titre' => 'Forfait',
                'sequentiel' => true,
                'options' => [
                    [
                        'id' => 'forfait_50',
                        'titre' => 'Weekend (Tarif 50 premiers inscrits)',
                        'description' => 'Un week-end de formations, d\'échanges et de cohésion dans une ambiance estivale dans les Landes afin de commencer à préparer au mieux 2027 👀☀️',
                        'montant' => 50,
                        'quota' => 50,
                    ],
                    [
                        'id' => 'forfait_60',
                        'titre' => 'Weekend (Tarif 100 premiers inscrits)',
                        'description' => 'Un week-end de formations, d\'échanges et de cohésion dans une ambiance estivale dans les Landes afin de commencer à préparer au mieux 2027 👀☀️',
                        'montant' => 60,
                        'quota' => 100,
                    ],
                    [
                        'id' => 'forfait_70',
                        'titre' => 'Weekend (Tarif 200 premiers inscrits)',
                        'description' => 'Un week-end de formations, d\'échanges et de cohésion dans une ambiance estivale dans les Landes afin de commencer à préparer au mieux 2027 👀☀️',
                        'montant' => 70,
                        'quota' => 200,
                    ],
                    [
                        'id' => 'forfait_80',
                        'titre' => 'Weekend (Tarif 300 premiers inscrits)',
                        'description' => 'Un week-end de formations, d\'échanges et de cohésion dans une ambiance estivale dans les Landes afin de commencer à préparer au mieux 2027 👀☀️',
                        'montant' => 80,
                        'quota' => 300,
                    ],
                    [
                        'id' => 'forfait_100',
                        'titre' => 'Weekend',
                        'description' => 'Un week-end de formations, d\'échanges et de cohésion dans une ambiance estivale dans les Landes afin de commencer à préparer au mieux 2027 👀☀️',
                        'montant' => 100,
                    ],
                ],
            ],
            [
                'cle' => 'transport',
                'titre' => 'Besoin d’un transport ?',
                'options' => [
                    [
                        'id' => 'avec_transport',
                        'recap_label' => 'Avec transport (train/bus)',
                        'titre' => 'Avec Transport',
                        'montant' => 20,
                        'description' => 'Aller-retour depuis 35 grandes villes jusqu’au site de l’Esti’JEM (la sélection de votre ville de départ se fait à l’étape suivante)',
                    ],
                    [
                        'id' => 'sans_transport',
                        'titre' => 'Je viens et repars en autonomie.',
                    ],
                ],
            ],
            [
                'type' => SelectFieldFormType::FIELD_NAME,
                'cle' => 'packageCity',
                'dependence' => ['avec_transport'],
                'titre' => 'Votre ville de départ et de retour',
                'placeholder' => 'Sélectionnez votre ville',
                'options' => [
                    'Amiens',
                    'Angers',
                    'Arras',
                    'Besançon',
                    'Brest',
                    'Bruxelles',
                    'Caen',
                    'Clermont-Ferrand',
                    'Dijon',
                    'Grenoble',
                    'La Rochelle',
                    'Le Havre',
                    'Le Mans',
                    'Lille',
                    'Limoges',
                    'Londres',
                    'Lyon',
                    'Marseille',
                    'Metz',
                    'Montpellier',
                    'Nancy',
                    'Nantes',
                    'Nice',
                    'Nîmes',
                    'Orléans',
                    'Paris',
                    'Pau',
                    'Perpignan',
                    'Poitiers',
                    'Reims',
                    'Rennes',
                    'Rouen',
                    'Saint-Etienne',
                    'Strasbourg',
                    'Toulouse',
                    'Tours',
                ],
            ],
            [
                'cle' => 'packageDepartureTime',
                'dependence' => ['avec_transport'],
                'titre' => 'Vos préférences de départ',
                'options' => [
                    [
                        'titre' => 'Matin',
                    ],
                    [
                        'titre' => 'Après-midi',
                    ],
                    [
                        'titre' => 'Soirée',
                        'description' => 'Sous réserves d’options et qu’ils peuvent être basculés sur le départ de l’après-midi le cas échéant',
                    ],
                ],
            ],
            [
                'cle' => 'packageDonation',
                'titre' => 'Donner au fond de solidarité',
                'description' => 'Le fonds de solidarité est alimenté volontairement par celles et ceux qui le souhaitent lors de leur inscription. Votre contribution permet de garantir l’accessibilité de l’événement au plus grand nombre. Nous vous remercions d’avance pour votre geste.',
                'options' => [
                    [
                        'id' => 'don_50',
                        'titre' => 'Je donne',
                        'montant' => 50,
                    ],
                    [
                        'id' => 'don_40',
                        'titre' => 'Je donne',
                        'montant' => 40,
                    ],
                    [
                        'id' => 'don_30',
                        'titre' => 'Je donne',
                        'montant' => 30,
                    ],
                    [
                        'id' => 'don_20',
                        'titre' => 'Je donne',
                        'montant' => 20,
                    ],
                    [
                        'id' => 'don_0',
                        'titre' => 'Je ne donne pas',
                        'montant' => 0,
                    ],
                ],
            ],
        ];

        $this->setReference('event-national-5', $event);

        $manager->persist($event = new NationalEvent());

        $event->setName('Meeting NRP');
        $event->alertEnabled = true;
        $event->type = NationalEventTypeEnum::NRP;
        $event->alertTitle = 'Venez nombreux !';
        $event->inscriptionEditDeadline = new \DateTime('+1 month');
        $event->startDate = new \DateTime('-1.6 hour');
        $event->endDate = new \DateTime()->add(new \DateInterval('P1M2D'));
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');
        $event->textIntro = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textHelp = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->intoImage = new UploadableFile();
        $event->intoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/renaissance/burex-renaissance.jpg', 'nrp.jpg', 'image/jpg', null, true));

        $event->ogImage = new UploadableFile();
        $event->ogImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/renaissance/burex-renaissance.jpg', 'nrp.jpg', 'image/jpg', null, true));

        $event->alertLogoImage = new UploadableFile();
        $event->alertLogoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/renaissance/burex-renaissance.jpg', 'nrp.jpg', 'image/jpg', null, true));

        $event->packageConfig = [
            [
                'cle' => 'visitDay',
                'titre' => 'Choisissez votre soirée',
                'description' => 'À vous de personnalisée votre soirée ! Première partie ou deuxième, voir les deux. Côté papilles, un cocktail dinatoire sera servi durant l’entracte commun.',
                'options' => [
                    [
                        'id' => 'partie-1',
                        'titre' => 'Première partie (18h - 20h45)',
                        'description' => 'La première partie de soirée sera rythmée par des interventions inspirantes et des témoignages engagés.',
                        'montant' => 20,
                        'quota' => 6,
                    ],
                    [
                        'id' => 'partie-2',
                        'titre' => 'Deuxième partie (21h15 - Minuit)',
                        'description' => 'La deuxième partie de soirée sera placée sous le signe de la fête avec des performances artistiques et un concert final endiablé.',
                        'montant' => 20,
                        'quota' => 10,
                    ],
                    [
                        'id' => 'partie-1-et-2',
                        'titre' => 'Deux parties (18h - Minuit)',
                        'description' => 'La totale immersion dans le Meeting NRP avec les deux parties de soirée.',
                        'montant' => 30,
                        'quota' => ['partie-1', 'partie-2'],
                    ],
                ],
            ],
            [
                'type' => PlaceChoiceFieldFormType::FIELD_NAME,
                'cle' => 'partie1place',
                'titre' => 'Choisissez votre place - Première partie',
                'description' => 'Choisissez votre place idéale pour cette première partie. Le placement est numéroté pour votre confort.',
                'dependence' => ['partie-1', 'partie-1-et-2'],
            ],
            [
                'type' => PlaceChoiceFieldFormType::FIELD_NAME,
                'cle' => 'partie2place',
                'titre' => 'Choisissez votre place - Deuxième partie',
                'description' => 'Choisissez votre place idéale pour cette deuxième partie. Le placement est numéroté pour votre confort.',
                'dependence' => ['partie-2', 'partie-1-et-2'],
                'places_reservees' => [
                    'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10', 'A11', 'A12', 'A13', 'A14', 'A15',
                    'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10', 'B11', 'B12', 'B13', 'B14', 'B15',
                    'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10', 'C11', 'C12', 'C13', 'C14', 'C15',
                    'D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7', 'D8', 'D9', 'D10', 'D11', 'D12', 'D13', 'D14', 'D15',
                ],
            ],
        ];

        $this->setReference('event-national-6', $event);

        $manager->flush();
    }
}
