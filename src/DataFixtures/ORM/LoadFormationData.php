<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Formation\Axe;
use AppBundle\Entity\Formation\Module;
use AppBundle\Entity\Formation\Path;
use AppBundle\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\File;

class LoadFormationData extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $storage->put('images/espace-formation.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Image pour l\'espace formation', 'espace-formation.jpg', $mediaFile);

        $manager->persist($path1 = $this->createPath('Parcours 1'));
        $manager->persist($path2 = $this->createPath('Parcours 2'));

        $manager->persist($axe1 = $this->createAxe($path1, 'Premier axe', $media));
        $manager->persist($axe2 = $this->createAxe($path1, 'Deuxième axe', $media));
        $manager->persist($axe3 = $this->createAxe($path2, 'Premier axe', $media));
        $manager->persist($axe4 = $this->createAxe($path2, 'Deuxième axe', $media));

        $manager->persist($this->createModule($axe1, 'Premier article du premier axe', $media));
        $manager->persist($this->createModule($axe1, 'Deuxième article du premier axe', $media));
        $manager->persist($this->createModule($axe1, 'Troisième article du premier axe', $media));

        $manager->persist($this->createModule($axe2, 'Premier article du deuxième axe', $media));
        $manager->persist($this->createModule($axe2, 'Deuxième article du deuxième axe', $media));
        $manager->persist($this->createModule($axe2, 'Troisième article du deuxième axe', $media));

        $manager->persist($this->createModule($axe3, 'Premier article du troisième axe', $media));
        $manager->persist($this->createModule($axe3, 'Deuxième article du troisième axe', $media));
        $manager->persist($this->createModule($axe3, 'Troisième article du troisième axe', $media));

        $manager->persist($this->createModule($axe4, 'Premier article du quatrième axe', $media));
        $manager->persist($this->createModule($axe4, 'Deuxième article du quatrième axe', $media));
        $manager->persist($this->createModule($axe4, 'Troisième article du quatrième axe', $media));

        $manager->flush();
    }

    private function createAxe(Path $path, string $title, Media $media): Axe
    {
        $axe = new Axe();
        $axe->setPath($path);
        $axe->setTitle($title);
        $axe->setDescription($this->faker->text('200'));
        $axe->setMedia($media);
        $axe->setDisplayMedia(true);
        $axe->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $axe;
    }

    private function createModule(Axe $axe, string $title, Media $media): Module
    {
        $module = new Module();
        $module->setAxe($axe);
        $module->setTitle($title);
        $module->setDescription($this->faker->text(200));
        $module->setMedia($media);
        $module->setDisplayMedia(true);
        $module->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $module;
    }

    private function createPath(string $title): Path
    {
        $path = new Path();
        $path->setTitle($title);
        $path->setDescription(<<<EOT
    Découvrez maintenant votre parcours personnalisé.
    Les modules sont numérotés pour vous permettre de
    compléter / renforcer vos compétences par ordre de priorité.
EOT
        );

        return $path;
    }
}
