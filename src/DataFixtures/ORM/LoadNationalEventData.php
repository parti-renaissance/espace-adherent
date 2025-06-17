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
                    'id' => 'gratuit',
                    'jours_ids' => ['jour_1_et_2', 'jour_2'],
                    'titre' => 'Je viens par mes propres moyens',
                ],
            ],
        ];

        $this->setReference('event-national-3', $event);

        $manager->flush();
    }
}
