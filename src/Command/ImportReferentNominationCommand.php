<?php

namespace AppBundle\Command;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Media;
use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentArea;
use AppBundle\Repository\MediaRepository;
use AppBundle\Repository\ReferentAreaRepository;
use AppBundle\Repository\ReferentRepository;
use AppBundle\ValueObject\Genders;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class ImportReferentNominationCommand extends ContainerAwareCommand
{
    private const MEDIAS_INFO = [
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

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ReferentAreaRepository
     */
    private $referentAreaRepository;

    /**
     * @var ReferentRepository
     */
    private $referentRepository;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

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
          ->setDescription(
            'Import referent and referent area from file store in Google Storage'
          )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->referentAreaRepository = $this->em->getRepository(ReferentArea::class);
        $this->referentRepository = $this->em->getRepository(Referent::class);
        $this->mediaRepository = $this->em->getRepository(Media::class);
        $this->mediaFactory = $this->getContainer()->get(MediaFactory::class);
        $this->storage = $this->getContainer()->get('app.storage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $rows = $this->parseCSV($input->getArgument('fileUrl'), 'r');
        } catch (FileNotFoundException $exception) {
            $output->writeln(
              sprintf('%s file not found', $input->getArgument('fileUrl'))
            );

            return 1;
        }

        $this->em->beginTransaction();

        $this->createAndPersistReferentArea($this->parseAreas($rows));
        $this->createAndPersistReferent($rows, $this->persistMedias());

        $this->em->flush();
        $this->em->commit();

        $output->writeln('Referents load OK');
    }

    private function parseCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('% not found', $filename));
        }

        while (false !== ($data = fgetcsv($handle, 10000, ';'))) {
            $row = array_map('trim', $data);
            $rows[] = [
              'region_name' => $row[0],
              'area_name' => $row[1],
              'area_code' => $row[2],
              'area_zip_code' => $row[3],
              'referent_firstname' => $row[4],
              'referent_lastname' => $row[5],
              'referent_status' => $row[6],
              'referent_age' => $row[7],
              'referent_sex' => $row[8],
            ];
        }
        fclose($handle);

        return $rows;
    }

    private function persistMedias(): array
    {
        $medias = [];
        foreach (self::MEDIAS_INFO as $item) {
            if ($this->mediaRepository->findOneBy(['name' => $item['name']])) {
                continue;
            }
            $mediaFile = new File(sprintf(
              '%s/app/data/dist/%s',
              $this->getContainer()->getParameter('kernel.project_dir'),
              $item['path']
            ));
            $this->storage->put(
              'images/'.$item['path'],
              file_get_contents($mediaFile->getPathname())
            );
            $media = $this->mediaFactory->createFromFile(
                $item['name'],
                $item['path'],
                $mediaFile
            );
            $this->em->persist($media);
            $medias[$item['sex']][] = $media;
        }
        $this->em->flush();

        return $medias;
    }

    private function parseAreas(array $rows): array
    {
        $areas = [];
        foreach ($rows as $row) {
            if ('75' === substr($row['area_code'], 0, 2)) {
                $parisDistrictZip = [$row['area_code']];
                if (false !== strpos($row['area_code'], ',')) {
                    $parisDistrictZip = explode(',', $row['area_code']);
                }

                foreach ($parisDistrictZip as $zipCode) {
                    $areas[$zipCode] = [
                          'name' => sprintf(
                          'Paris %s',
                          '0' === substr($zipCode, -2, 1)
                            ? substr($zipCode, -1)
                            : substr($zipCode, -2)
                        ),
                        'type' => 'arrondissement',
                    ];
                }
            } else {
                $areas[$row['area_code']] = [
                  'name' => $row['area_name'],
                  'type' => 'departement',
                ];
            }
        }

        return $areas;
    }

    private function createAndPersistReferentArea(array $areas): void
    {
        foreach ($areas as $areaCode => $area) {
            if ($this->referentAreaRepository->findReferentArea($areaCode)) {
                continue;
            }
            $referentArea = new ReferentArea();
            $referentArea->setName($area['name']);
            $referentArea->setAreaCode($areaCode);
            $referentArea->setAreaType($area['type']);
            $referentArea->setKeywords([$area['name']]);

            $this->em->persist($referentArea);
        }
        $this->em->flush();
    }

    private function createAndPersistReferent(array $rows, array $medias): void
    {
        foreach ($rows as $row) {
            if ($this->referentRepository->findOneBy(
                [
                  'firstName' => $row['referent_firstname'],
                  'lastName' => $row['referent_lastname'],
                ]
            )
            ) {
                continue;
            }
            $mediaPossibilities = $medias[$row['referent_sex']];
            shuffle($mediaPossibilities);

            $referent = new Referent();
            $referent->setMedia($mediaPossibilities[0]);
            $referent->setFirstName($row['referent_firstname']);
            $referent->setLastName($row['referent_lastname']);
            $referent->setAreaLabel($row['area_name']);
            $referent->setGender(
              'M' === $row['referent_sex'] ? Genders::MALE : Genders::FEMALE
            );
            $areaCodes = [$row['area_code']];

            if (false !== strpos($row['area_code'], ',')) {
                $areaCodes = explode(',', $row['area_code']);
            }

            foreach ($areaCodes as $areaCode) {
                if ($area = $this->referentAreaRepository->findReferentArea(
                    $areaCode
                )
                ) {
                    $referent->addArea($area);
                }
            }

            $this->em->persist($referent);
        }
    }
}
