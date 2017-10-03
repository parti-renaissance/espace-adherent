<?php

namespace AppBundle\Command;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Media;
use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentArea;
use AppBundle\Repository\MediaRepository;
use AppBundle\Repository\ReferentAreaRepository;
use AppBundle\ValueObject\Genders;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImportReferentNominationCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ReferentAreaRepository
     */
    private $referentAreaRepository;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var array
     */
    private $mediasInfo;

    /**
     * @var MediaFactory
     */
    private $mediaFactory;

    /**
     * @var Filesystem
     */
    private $storage;

    protected function configure()
    {
        $this
            ->setName('app:import:referent-nomination')
            ->addArgument('fileUrl', InputArgument::REQUIRED)
            ->setDescription('Import referent and referent area from file store in Google Storage')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->referentAreaRepository = $this->em->getRepository(ReferentArea::class);
        $this->mediaRepository = $this->em->getRepository(Media::class);
        $this->mediasInfo = [
            [
                'path' => 'avatar_femme_01.jpg',
                'name' => 'Avatar femme 1',
                'sex' => 'F',
            ],
            [
                'path' => 'avatar_femme_02.jpg',
                'name' => 'Avatar femme 2',
                'sex' => 'F',
            ],
            [
                'path' => 'avatar_homme_01.jpg',
                'name' => 'Avatar homme 1',
                'sex' => 'M',
            ],
            [
                'path' => 'avatar_homme_02.jpg',
                'name' => 'Avatar homme 2',
                'sex' => 'M',
            ],
        ];
        $this->mediaFactory = $this->getContainer()->get('app.content.media_factory');
        $this->storage = $this->getContainer()->get('app.storage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rows = file($input->getArgument('fileUrl'));
        if ($rows === false) {
            $output->writeln('Error : File Not Found');

            return 1;
        }

        $areas = [];
        $medias = [];

        $this->em->beginTransaction();

        foreach ($this->mediasInfo as $item) {
            $mediaFile = new File($this->getContainer()->getParameter('kernel.root_dir').'/data/dist/'.$item['path']);
            $this->storage->put('images/'.$item['path'], file_get_contents($mediaFile->getPathname()));
            $media = $this->mediaFactory->createFromFile($item['name'], $item['path'], $mediaFile);
            $this->em->persist($media);
            $medias[$item['sex']][] = $media;
        }
        $this->em->flush();

        foreach ($rows as $row) {
            $cell = array_map('trim', explode(';', $row));

            if ('75' === substr($cell[2], 0, 2) && 5 <= strlen($cell[2])) {
                $parisDistrictZip = [$cell[2]];
                if (strpos($cell[2], ',') !== false) {
                    $parisDistrictZip = explode(',', $cell[2]);
                }
                foreach ($parisDistrictZip as $zipCode) {
                    $areas[$zipCode] = [
                        'name' => sprintf('Paris %s',
                            substr($zipCode, -2, 1) === '0'
                                ? substr($zipCode, -1)
                                : substr($zipCode, -2)
                        ),
                        'type' => 'arrondissement',
                    ];
                }
            } else {
                $areas[$cell[2]] = [
                    'name' => $cell[1],
                    'type' => 'departement',
                ];
            }
        }

        foreach ($areas as $areaCode => $area) {
            $referentArea = new ReferentArea();
            $referentArea->setName($area['name']);
            $referentArea->setAreaCode($areaCode);
            $referentArea->setAreaType($area['type']);
            $referentArea->setKeywords([$area['name']]);

            $this->em->persist($referentArea);
        }
        $this->em->flush();

        foreach ($rows as $row) {
            $cell = array_map('trim', explode(';', $row));
            $mediaPossibilities = $medias[$cell[8]];
            shuffle($mediaPossibilities);

            $referent = new Referent();
            $referent->setMedia($mediaPossibilities[0]);
            $referent->setFirstName($cell[4]);
            $referent->setLastName($cell[5]);
            $referent->setAreaLabel($cell[1]);
            $referent->setGender($cell[8] === 'M' ? Genders::MALE : Genders::FEMALE);
            $areaCodes = [$cell[2]];

            if (strpos($cell[2], ',') !== false) {
                $areaCodes = explode(',', $cell[2]);
            }

            foreach ($areaCodes as $areaCode) {
                if ($area = $this->referentAreaRepository->findReferentArea($areaCode)) {
                    $referent->addArea($area);
                }
            }

            $this->em->persist($referent);
        }

        $this->em->flush();
        $this->em->commit();

        $output->writeln('Referents load OK');
    }
}
