<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\BoardMember\Role;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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

    protected function configure()
    {
        $this
          ->setName('app:import:board-member')
          ->addArgument('fileUrl', InputArgument::REQUIRED)
          ->setDescription('Import board member from CSV file')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->boardMemberRepository = $this->em->getRepository(BoardMember::class);
        $this->adherentRepository = $this->em->getRepository(Adherent::class);
        $this->roleRepository = $this->em->getRepository(Role::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $rows = $this->parseCSV($input->getArgument('fileUrl'));
        } catch (FileNotFoundException $e) {
            $output->writeln(sprintf('%s not found'), $input->getArgument('fileUrl'));

            return 1;
        }

        $this->em->beginTransaction();

        $this->createAndPersistBoardMember($rows);

        $this->em->flush();
        $this->em->commit();

        $output->writeln('Import finish');
    }

    private function parseCSV(string $filename): array
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

    private function createAndPersistBoardMember(array $rows): void
    {
        foreach ($rows as $row) {
            if (null === ($adherent = $this->adherentRepository->findByEmail($row['email']))) {
                continue;
            }
            if ($adherent->isBoardMember()) {
                continue;
            }

            $adherent->setBoardMember(
                $this->getTypeOfArea($row['area']['country'], $row['area']['country_type']),
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
