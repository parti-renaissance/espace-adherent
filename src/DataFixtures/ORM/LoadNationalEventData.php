<?php

namespace App\DataFixtures\ORM;

use App\Entity\NationalEvent\NationalEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadNationalEventData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($event = new NationalEvent());

        $event->setName('Event national 1');
        $event->alertEnabled = true;
        $event->alertTitle = 'Venez nombreux !';
        $event->startDate = new \DateTime('+1 month');
        $event->endDate = new \DateTime('+1.5 month');
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');

        $event->textIntro = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textHelp = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->intoImagePath = '/donation-bg.jpg';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->source = '123xyz';

        $this->setReference('event-national-1', $event);

        $manager->persist($event = new NationalEvent());

        $event->setName('Event national 2');

        $event->startDate = new \DateTime('+1 month');
        $event->endDate = new \DateTime('+1.5 month');
        $event->ticketStartDate = new \DateTime('-1 day');
        $event->ticketEndDate = new \DateTime('+1 month');

        $event->textIntro = '<p>Voici un nouvel event</p>';
        $event->textHelp = '<p>Il suffit de remplir le formulaire</p>';
        $event->textConfirmation = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->textTicketEmail = '<p>Lorem ipsum dolor sit amet consectetur. Nunc cras porta sed nullam eget at.</p>';
        $event->intoImagePath = '/donation-bg.jpg';
        $event->imageTicketEmail = '/donation-bg.jpg';
        $event->subjectTicketEmail = 'Meeting arrive bientôt !';
        $event->source = '123xyz';

        $this->setReference('event-national-2', $event);

        $manager->flush();
    }
}
