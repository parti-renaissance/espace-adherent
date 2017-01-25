<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Content\HomeBlockFactory;
use AppBundle\Content\MediaFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadHomeBlockData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var MediaFactory
     */
    private $mediaFactory;

    /**
     * @var HomeBlockFactory
     */
    private $homeBlockFactory;

    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * @var array
     */
    private $mediasRegistry;

    public function load(ObjectManager $manager)
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->mediaFactory = $this->container->get('app.content.media_factory');
        $this->homeBlockFactory = $this->container->get('app.content.home_block_factory');
        $this->storage = $this->container->get('app.storage');

        $this->loadMedias();
        $this->loadHomeBlocks();
    }

    private function loadMedias()
    {
        $repository = $this->em->getRepository('AppBundle:Media');

        foreach (self::$data as $homeBlockData) {
            if (isset($this->mediasRegistry[$homeBlockData['path']])) {
                continue;
            }

            if ($media = $repository->findOneBy(['path' => $homeBlockData['path']])) {
                $this->mediasRegistry[$media->getPath()] = $media;
                continue;
            }

            $mediaFile = new File(__DIR__.'/../../../../app/data/dist/'.$homeBlockData['path']);
            $this->storage->put('images/'.$homeBlockData['path'], file_get_contents($mediaFile->getPathname()));

            $media = $this->mediaFactory->createFromFile(
                $homeBlockData['pathTitle'],
                $homeBlockData['path'],
                $mediaFile
            );

            $this->em->persist($media);

            $this->mediasRegistry[$media->getPath()] = $media;
        }

        $this->em->flush();
    }

    private function loadHomeBlocks()
    {
        $repository = $this->em->getRepository('AppBundle:HomeBlock');

        foreach (self::$data as $i => $homeBlockData) {
            if ($repository->findOneBy(['position' => $i])) {
                continue;
            }

            $this->em->persist($this->homeBlockFactory->createFromArray([
                'position' => $i,
                'positionName' => $homeBlockData['positionName'],
                'type' => $homeBlockData['type'],
                'title' => $homeBlockData['title'],
                'subtitle' => $homeBlockData['subtitle'],
                'link' => $homeBlockData['link'],
                'media' => $this->mediasRegistry[$homeBlockData['path']],
            ]));
        }

        $this->em->flush();
    }

    private static $data = [
        [
            'positionName' => 'Bannière principale',
            'type' => 'article',
            'title' => '« Je viens échanger, comprendre et construire. »',
            'subtitle' => 'Emmanuel Macron a scilloné la Guadeloupe, la Martinique et la Guyane pendant 5 jours.',
            'path' => 'guadeloupe.jpg',
            'pathTitle' => 'Guadeloupe',
            'link' => '/article/outre-mer',
        ],
        [
            'positionName' => 'Bloc 1',
            'type' => 'article',
            'title' => 'Tribune de Richard Ferrand',
            'subtitle' => null,
            'path' => 'richardferrand.jpg',
            'pathTitle' => 'Richard Ferrand',
            'link' => 'http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/',
        ],
        [
            'positionName' => 'Bloc 2',
            'type' => 'video',
            'title' => 'Signez l’appel « Elles Marchent »',
            'subtitle' => null,
            'path' => 'ellesmarchent.jpg',
            'pathTitle' => 'Elles marchent',
            'link' => 'https://en-marche.fr/signez-lappel-marchent/',
        ],
        [
            'positionName' => 'Bloc 3',
            'type' => 'video',
            'title' => 'Revivez le grand rassemblement du 10 décembre !',
            'subtitle' => null,
            'path' => '10decembre.jpg',
            'pathTitle' => '10 décembre',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
        [
            'positionName' => 'Bloc 4',
            'type' => 'article',
            'title' => 'Tribune de Richard Ferrand',
            'subtitle' => null,
            'path' => 'macron.jpg',
            'pathTitle' => 'Macron',
            'link' => 'http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/',
        ],
        [
            'positionName' => 'Bloc 5',
            'type' => 'video',
            'title' => 'Signez l’appel « Elles Marchent »',
            'subtitle' => null,
            'path' => 'ellesmarchent.jpg',
            'pathTitle' => 'Elles marchent',
            'link' => 'https://en-marche.fr/signez-lappel-marchent/',
        ],
        [
            'positionName' => 'Bloc 6',
            'type' => 'video',
            'title' => 'Revivez le grand rassemblement du 10 décembre !',
            'subtitle' => null,
            'path' => '10decembre.jpg',
            'pathTitle' => '10 décembre',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
        [
            'positionName' => 'Bannière bas de page',
            'type' => 'article',
            'title' => '3 boucliers pour protéger la France',
            'subtitle' => null,
            'path' => 'proteger-la-france.jpg',
            'pathTitle' => '3 boucliers pour protéger la France',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
    ];
}
