<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\BoardMember\BoardMember;
use App\Entity\BoardMember\Role;
use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportBoardMemberCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EntityRepository
     */
    private $boardMemberRepository;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var EntityRepository
     */
    private $roleRepository;

    private $notFoundEmails;

    protected function configure()
    {
        $this
            ->setName('app:import:board-member')
            ->addOption('csvtypeformurl', 'tfcsv', InputArgument::OPTIONAL, 'URL of type form CSV result', null)
            ->addOption('othercsv', 'csv', InputArgument::OPTIONAL, 'URL of CSV File', null)
            ->setDescription('Import board member from CSV file')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->boardMemberRepository = $this->em->getRepository(BoardMember::class);
        $this->adherentRepository = $this->em->getRepository(Adherent::class);
        $this->roleRepository = $this->em->getRepository(Role::class);
        $this->notFoundEmails = [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileNameTypeFormCSV = null;
        $fileNameOtherCSV = null;
        $typeFormRows = [];
        $otherCSVRows = [];

        if (null === ($fileNameTypeFormCSV = $input->getOption(
                'csvtypeformurl'
            )) && null === ($fileNameOtherCSV = $input->getOption('othercsv'))) {
            throw new LogicException('Pass at leat one URL of file');
        }

        try {
            if ($fileNameTypeFormCSV) {
                $typeFormRows = $this->parseTypeFormCSV($fileNameTypeFormCSV);
            }

            if ($fileNameOtherCSV) {
                $otherCSVRows = $this->parseOtherCSV($fileNameOtherCSV);
            }
        } catch (FileNotFoundException $e) {
            $output->writeln(sprintf('%s not found'), $e->getFile());

            return 1;
        }

        $rows = array_merge($typeFormRows, $otherCSVRows);

        $this->em->beginTransaction();

        $this->createAndPersistBoardMember($rows);

        $this->em->flush();
        $this->em->commit();

        $output->writeln('Import finish');
        if ($this->notFoundEmails) {
            $output->writeln('The following email adresses were not found in DB');
            foreach ($this->notFoundEmails as $email) {
                $output->writeln($email);
            }
        }
    }

    private function parseTypeFormCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('% not found', $filename));
        }

        $isFirstRow = true;
        while (false !== ($data = fgetcsv($handle, 100000, ';'))) {
            if (true === $isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            $row = array_map('trim', $data);
            $rows[] = [
                'email' => $row[5],
                'execute_mandate' => $row[11],
                'roles' => [
                    'mayor_less' => empty($row[12]),
                    'president_less' => empty($row[13]),
                    'consular' => empty($row[14]),
                    'other_role' => empty($row[15]),
                ],
                'area' => [
                    'country' => $row[16],
                    'country_type' => $row[17],
                ],
            ];
        }
        fclose($handle);
        array_pop($rows);

        return $rows;
    }

    private function parseOtherCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('% not found', $filename));
        }

        $isFirstRow = true;
        while (false !== ($data = fgetcsv($handle, 100000, ';'))) {
            if (true === $isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            $row = array_map('trim', $data);
            $rows[] = [
                'email' => $row[9],
                'execute_mandate' => true,
                'roles' => [
                    'mayor_less' => 'Maire >50k' === $row[0] ? true : false,
                    'president_less' => 'EPCI > 100k' === $row[0] ? true : false,
                    'minister' => 'Gouvernement' === $row[0] ? true : false,
                    'deputy' => 'Députés' === $row[0] ? true : false,
                    'european_deputy' => 'Députés européens' === $row[0] ? true : false,
                    'referent' => 'Referent' === $row[0] ? true : false,
                    'other_role' => false,
                ],
            ];
        }
        fclose($handle);

        array_pop($rows);

        return $rows;
    }

    private function createAndPersistBoardMember(array $rows): void
    {
        foreach ($rows as $row) {
            if (!$adherent = $this->adherentRepository->findOneByEmail($row['email'])) {
                $this->notFoundEmails[] = $row['email'];

                continue;
            }

            if ($adherent->isBoardMember()) {
                continue;
            }

            if (isset($row['area'])) {
                $area = $this->getTypeOfArea($row['area']['country'], $row['area']['country_type']);
            } else {
                $area = implode(
                    ',',
                    [
                        $adherent->getPostAddress()->getPostalCode(),
                        substr($adherent->getPostAddress()->getPostalCode(), 0, 2),
                    ]
                );
            }

            $adherent->setBoardMember(
                $area,
                $this->getRoles($row)
            );

            $this->em->persist($adherent);
        }
    }

    private function getTypeOfArea(string $area, $areaType): string
    {
        $areaCode = explode(';', $area)[1] ?? null;
        $type = BoardMember::AREA_ABROAD;

        if ('FR' === $areaCode && 'France Métropolitaine' === $areaType) {
            $type = BoardMember::AREA_FRANCE_METROPOLITAN;
        }

        if (('FR' === $areaCode && 'Outre-Mer' === $areaType) || 'NC' === $areaCode) {
            $type = BoardMember::AREA_OVERSEAS_FRANCE;
        }

        return $type;
    }

    private function getRoles(array $row): ArrayCollection
    {
        $roles = new ArrayCollection();

        if (true === $row['roles']['other_role']) {
            $roles->add($this->roleRepository->findOneBy(['code' => 'adherent']));

            return $roles;
        }

        foreach ($row['roles'] as $code => $hasRole) {
            if (false === $hasRole || 'other_role' === $code) {
                continue;
            }

            $roles->add($this->roleRepository->findOneBy(['code' => $code]));
        }

        return $roles;
    }
}
