<?php

namespace App\DataFixtures\ORM;

use App\Entity\Consultation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadConsultationData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createConsultation(
            'Consultation sur la transition écologique',
            <<<MARKDOWN
                    # Lorem ipsum

                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                MARKDOWN,
            'https://parti-renaissance.fr'
        ));

        $manager->persist($this->createConsultation(
            'Consultation désactivée',
            <<<MARKDOWN
                    # Lorem ipsum

                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                MARKDOWN,
            'https://en-marche.fr',
            false
        ));

        $manager->flush();
    }

    private function createConsultation(
        string $title,
        string $content,
        string $url,
        bool $isPublished = true
    ): Consultation {
        $consultation = new Consultation();
        $consultation->setTitle($title);
        $consultation->setContent($content);
        $consultation->setUrl($url);
        $consultation->setPublished($isPublished);

        return $consultation;
    }
}
