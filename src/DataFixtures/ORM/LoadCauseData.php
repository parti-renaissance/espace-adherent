<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCauseData extends Fixture implements DependentFixtureInterface
{
    public const CAUSE_1_UUID = '55056e7c-2b5f-4ef6-880e-cde0511f79b2';
    public const CAUSE_2_UUID = 'fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8';
    public const CAUSE_3_UUID = '44249b1d-ea10-41e0-b288-5eb74fa886ba';

    private $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $cause1 = $this->createCause(
            self::CAUSE_1_UUID,
            'Cause pour la culture',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-culture'),
            $this->getReference('adherent-1'),
            true
        );

        $cause2 = $this->createCause(
            self::CAUSE_2_UUID,
            'Cause pour l\'education',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-education'),
            $this->getReference('adherent-3'),
            true
        );

        $cause3 = $this->createCause(
            self::CAUSE_3_UUID,
            'Cause pour la justice',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            $this->getReference('coalition-justice'),
            $this->getReference('adherent-3'),
            false
        );

        $manager->persist($cause1);
        $manager->persist($cause2);
        $manager->persist($cause3);

        $manager->flush();
    }

    public function createCause(
        string $uuid,
        string $name,
        string $description,
        Coalition $coalition,
        Adherent $author,
        bool $withImage = false
    ): Cause {
        $cause = new Cause(
            Uuid::fromString($uuid),
            $name,
            $description,
            $coalition,
            $author
        );

        if ($withImage) {
            $cause->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                null,
                true
            ));
            $this->imageManager->saveImage($cause);
        }

        return $cause;
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCoalitionData::class,
        ];
    }
}
