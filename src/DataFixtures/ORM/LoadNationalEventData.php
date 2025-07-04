<?php

namespace App\DataFixtures\ORM;

use App\Entity\NationalEvent\NationalEvent;
use App\Entity\UploadableFile;
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
        $event->endDate = (new \DateTime())->add(new \DateInterval('P1M2D'));
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
        $event->startDate = new \DateTime('+1 month');
        $event->endDate = (new \DateTime())->add(new \DateInterval('P1M2D'));
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
        $event->startDate = new \DateTime('+1 month');
        $event->endDate = (new \DateTime())->add(new \DateInterval('P1M2D'));
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

        $event->logoImage = new UploadableFile();
        $event->logoImage->setUploadFile(new UploadedFile(__DIR__.'/../../../public/images/campus/campus-illustration.png', 'campus-illustration.png', 'image/png', null, true));

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

        $event->transportConfiguration = [
            'jours' => [
                [
                    'id' => 'jour_1_et_2',
                    'titre' => "Le {$startDate} et le {$endDate}",
                    'description' => 'L’hébergement reste à la charge des participants que nous vous invitons à réserver de votre côté.',
                ],
                [
                    'id' => 'jour_2',
                    'titre' => "Seulement le {$endDate}",
                    'description' => 'L’essentiel du Campus se déroule sur la deuxième journée.',
                ],
            ],
            'transports' => [
                [
                    'id' => 'train',
                    'jours_ids' => ['jour_2'],
                    'recap_label' => 'Train aller-retour',
                    'quota' => 1000,
                    'titre' => 'Train (Paris >< Arras) Dimanche',
                    'montant' => 50,
                    'description' => 'Départ 7h45 à Paris gare du nord<br/>Retour à 17h45 à Paris gare du nord',
                ],
                [
                    'id' => 'bus',
                    'jours_ids' => ['jour_2'],
                    'recap_label' => 'Bus aller-retour',
                    'quota' => 600,
                    'titre' => 'Bus (Paris >< Arras) Dimanche',
                    'montant' => 20,
                    'description' => 'Départ 7h45 à Paris gare du nord<br/>Retour à 17h45 à Paris gare du nord',
                ],
                [
                    'id' => 'train_aller',
                    'jours_ids' => ['jour_2'],
                    'recap_label' => 'Train aller uniquement',
                    'quota' => 200,
                    'titre' => 'Train (Paris > Arras) Dimanche',
                    'montant' => 25,
                    'description' => 'Départ 7h45 à Paris gare du nord',
                ],
                [
                    'id' => 'gratuit',
                    'jours_ids' => ['jour_1_et_2', 'jour_2'],
                    'titre' => 'Je viens par mes propres moyens',
                ],
            ],
            'hebergements' => [
                [
                    'id' => 'chambre_individuelle',
                    'jours_ids' => ['jour_2'],
                    'recap_label' => 'Chambre individuelle',
                    'titre' => 'Chambre individuelle',
                    'montant' => 49,
                    'accompagnement' => true,
                    'pid_label' => 'Vous souhaitez partager votre chambre en couple ?',
                    'pid_description' => '<div>Si vous souhaitez partager votre chambre avec votre compagne ou votre compagnon, renseignez ici son numéro adhérent.</div>',
                    'description' => 'Je réserve une chambre individuelle',
                ],
                [
                    'id' => 'chambre_partagee',
                    'jours_ids' => ['jour_2'],
                    'recap_label' => 'Chambre partagée (à deux)',
                    'titre' => 'Chambre partagée (à deux)',
                    'montant' => 49,
                    'accompagnement' => true,
                    'pid_label' => 'Souhaitez-vous flècher votre partenaire de chambre ?',
                    'pid_description' => '<div>Vous pouvez indiquer ici le num\u00e9ro adh\u00e9rent ou code invitation d\u2019un autre participant avec qui vous souhaitez partager la chambre.<br/><br/>Par d\u00e9faut, nous choisirons un participant de m\u00eame civilit\u00e9 et d\u2019\u00e2ge similaire.</div>',
                    'description' => 'Je réserve une chambre partagée',
                ],
                [
                    'id' => 'gratuit',
                    'jours_ids' => ['jour_2'],
                    'titre' => 'Je n\'ai pas besoin d\'hébergement',
                    'description' => 'Je trouve un hébergement par mes propres moyens',
                ],
            ],
        ];

        $this->setReference('event-national-3', $event);

        $manager->flush();
    }
}
