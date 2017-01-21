<?php

namespace AppBundle\Command;

use AppBundle\Entity\Article;
use AppBundle\Entity\HomeBlock;
use AppBundle\Entity\LiveLink;
use AppBundle\Entity\Media;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentPrepareCommand extends ContainerAwareCommand
{
    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $mediasRegistry;

    protected function configure()
    {
        $this
            ->setName('app:content:prepare')
            ->setDescription('Populate the basic content entities to access the website');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->storage = $this->getContainer()->get('app.storage');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->mediasRegistry = [];

        $this->populateMedias();
        $this->populateHomeBlocks();
        $this->populateHomeLiveLinks();
        $this->populateDefaultArticle();
    }

    private function populateMedias()
    {
        $repository = $this->em->getRepository('AppBundle:Media');

        foreach (self::$homeBlocksData as $homeBlockData) {
            if (isset($this->mediasRegistry[$homeBlockData['path']])) {
                continue;
            }

            if ($media = $repository->findOneBy(['path' => $homeBlockData['path']])) {
                $this->mediasRegistry[$media->getPath()] = $media;
                continue;
            }

            $fixturePath = __DIR__.'/../../../app/data/dist/'.$homeBlockData['path'];
            $this->storage->put('images/'.$homeBlockData['path'], file_get_contents($fixturePath));

            $media = new Media();
            $media->setPath($homeBlockData['path']);
            $media->setFile(new UploadedFile($fixturePath, $homeBlockData['path']));
            $media->setName($homeBlockData['pathTitle']);

            $this->em->persist($media);

            $this->mediasRegistry[$media->getPath()] = $media;
        }

        $this->em->flush();
    }

    private function populateHomeBlocks()
    {
        $repository = $this->em->getRepository('AppBundle:HomeBlock');

        foreach (self::$homeBlocksData as $i => $homeBlockData) {
            if ($repository->findOneBy(['position' => $i])) {
                continue;
            }

            $homeBlock = new HomeBlock();
            $homeBlock->setMedia($this->mediasRegistry[$homeBlockData['path']]);
            $homeBlock->setTitle($homeBlockData['title']);
            $homeBlock->setSubtitle($homeBlockData['subtitle']);
            $homeBlock->setType($homeBlockData['type']);
            $homeBlock->setLink($homeBlockData['link']);
            $homeBlock->setPosition($i);
            $homeBlock->setPositionName($homeBlockData['positionName']);

            $this->em->persist($homeBlock);
        }

        $this->em->flush();
    }

    private function populateHomeLiveLinks()
    {
        $repository = $this->em->getRepository('AppBundle:LiveLink');

        if (!$repository->findOneBy(['title' => 'Guadeloupe'])) {
            $liveLink = new LiveLink();
            $liveLink->setPosition(1);
            $liveLink->setTitle('Guadeloupe');
            $liveLink->setLink('https://en-marche.fr/outre-mer-lun-piliers-de-richesse-culturelle/');

            $this->em->persist($liveLink);
            $this->em->flush();
        }

        if (!$repository->findOneBy(['title' => 'Le candidat du travail'])) {
            $liveLink = new LiveLink();
            $liveLink->setPosition(2);
            $liveLink->setTitle('Le candidat du travail');
            $liveLink->setLink('https://en-marche.fr/outre-mer-lun-piliers-de-richesse-culturelle/');

            $this->em->persist($liveLink);
            $this->em->flush();
        }
    }

    private function populateDefaultArticle()
    {
        if ($this->em->getRepository('AppBundle:Article')->findOneBy(['slug' => 'outre-mer'])) {
            return;
        }

        $article = new Article();
        $article->setMedia($this->mediasRegistry['macron.jpg']);
        $article->setContent(file_get_contents(__DIR__.'/../../../app/data/dist/content.md'));
        $article->setTitle('« Les outre-mer sont l’un des piliers de notre richesse culturelle. »');
        $article->setSlug('outre-mer');
        $article->setDescription('Emmanuel Macron s’est rendu du 17 au 21 décembre 2016 en Guadeloupe, Martinique et Guyane.');

        $this->em->persist($article);
        $this->em->flush();
    }

    private static $homeBlocksData = [
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
