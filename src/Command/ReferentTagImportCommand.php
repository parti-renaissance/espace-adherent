<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ReferentTagImportCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:referent-tags:import')
            ->addArgument('tagsUrl', InputArgument::REQUIRED)
            ->addArgument('referentsUrl', InputArgument::REQUIRED)
            ->setDescription('Import Referent Tags from CSV files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Referent Tags import.']);

        $this->em->beginTransaction();

        try {
            $this->importReferentTags($input, $output);
            $this->importReferentManagedAreas($input, $output);
            $this->initializeAdherentTags($output);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $output->writeln(['', 'Referent Tags imported successfully!']);
    }

    private function importReferentTags(InputInterface $input, OutputInterface $output): void
    {
        $tagsUrl = $input->getArgument('tagsUrl');

        $output->writeln(['', "Starting tags import from \"$tagsUrl\"."]);

        $count = 0;
        foreach ($this->parseCSV($tagsUrl) as $index => $row) {
            list($name, $code) = $row;

            if (empty($name)) {
                throw new \RuntimeException(sprintf('No label found for tag. (line %d)', $index + 2));
            }

            if (empty($code)) {
                throw new \RuntimeException(sprintf('No code found for tag "%s". (line %d)', $name, $index + 2));
            }

            $this->em->persist(new ReferentTag($name, $code));

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $output->writeln("Saved $count Referent tags.");
    }

    private function initializeAdherentTags(OutputInterface $output): void
    {
        $sql = <<<SQL
INSERT INTO adherent_referent_tag (adherent_id, referent_tag_id)
(
   SELECT adherent.id, tag.id
   FROM adherents adherent
   INNER JOIN referent_tags tag ON tag.code = IF(
       adherent.address_country != 'FR',
       adherent.address_country,
       CASE SUBSTRING(adherent.address_postal_code, 1, 2)
           -- Corsica
           WHEN '20' THEN IF(
               SUBSTRING(adherent.address_postal_code, 1, 3) IN ('200', '201'),
               '2A',
               '2B'
           )
           -- Paris district
           WHEN '75' THEN adherent.address_postal_code
           -- DOM
           WHEN '97' THEN IF(
               SUBSTRING(adherent.address_postal_code, 1, 3) IN ('97133', '97150'),
               adherent.address_postal_code,
               SUBSTRING(adherent.address_postal_code, 1, 3)
           )
           -- TOM
           WHEN '98' THEN IF(
               adherent.address_postal_code = '98000',
               'MC',
               SUBSTRING(adherent.address_postal_code, 1, 3)
           )
           -- Regular departement code
           ELSE SUBSTRING(adherent.address_postal_code, 1, 2)
       END
   )
);

-- Additional tags for Corsica (20)
INSERT INTO adherent_referent_tag (adherent_id, referent_tag_id)
(
    SELECT adherent.id, tag.id
    FROM adherents adherent
    INNER JOIN referent_tags tag ON tag.code = '20'
    WHERE adherent.address_country = 'FR' 
    AND SUBSTRING(adherent.address_postal_code, 1, 2) = '20'
);

-- Additional tags for Paris (75)
INSERT INTO adherent_referent_tag (adherent_id, referent_tag_id)
(
    SELECT adherent.id, tag.id
    FROM adherents adherent
    INNER JOIN referent_tags tag ON tag.code = '75'
    WHERE adherent.address_country = 'FR' 
    AND SUBSTRING(adherent.address_postal_code, 1, 2) = '75'
);
SQL;

        $output->writeln(['', 'Tagging adherents.']);

        $this->em->getConnection()->exec($sql);

        $output->writeln(['', 'Adherents tagged successfully.']);
    }

    private function importReferentManagedAreas(InputInterface $input, OutputInterface $output): void
    {
        $referentTags = $this->getReferentTags();

        $tagsUrl = $input->getArgument('referentsUrl');

        $output->writeln(['', "Starting Referent managed area import from \"$tagsUrl\"."]);

        $count = 0;
        foreach ($this->parseCSV($tagsUrl) as $index => $row) {
            list($uuid, $firstName, $lastName, $tags, $latitude, $longitude) = $row;

            $tags = array_map('trim', explode(',', $tags));

            if (empty($uuid)) {
                throw new \RuntimeException(sprintf('No uuid found for Adherent. (line %d)', $index + 2));
            }

            if (!$adherent = $this->findAdherent($uuid)) {
                throw new \RuntimeException(sprintf('No Adherent found with uuid "%s". (line %d)', $uuid, $index + 2));
            }

            if (empty($tags)) {
                throw new \RuntimeException(sprintf('No tag found for Adherent "%s %s". (line %d)', $firstName, $lastName, $index + 2));
            }

            $managedAreaTags = [];
            foreach ($tags as $tagCode) {
                if (!array_key_exists($tagCode, $referentTags)) {
                    throw new \RuntimeException(sprintf('No ReferentTag found with code "%s". (line %d)', $tagCode, $index + 2));
                }

                $managedAreaTags[] = $referentTags[$tagCode];
            }

            if ($latitude && $longitude) {
                $adherent->setReferentInfo($managedAreaTags, $latitude, $longitude);
            } else {
                $adherent->setReferentInfo($managedAreaTags);
            }

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $output->writeln("Saved $count Referent managed areas.");
    }

    private function parseCSV(string $filepath): array
    {
        if (false === ($handle = fopen($filepath, 'r'))) {
            throw new FileNotFoundException(sprintf('File "%s" was not found', $filename));
        }

        $isFirstRow = true;
        while (false !== ($data = fgetcsv($handle, 0, ','))) {
            if (true === $isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            $rows[] = array_map('trim', $data);
        }

        fclose($handle);

        return $rows ?? [];
    }

    private function getReferentTags(): array
    {
        /** @var ReferentTag $referentTag */
        foreach ($this->em->getRepository(ReferentTag::class)->findAll() as $referentTag) {
            $referentTags[$referentTag->getCode()] = $referentTag;
        }

        return $referentTags ?? [];
    }

    private function findAdherent(string $uuid): ?Adherent
    {
        return $this->em->getRepository(Adherent::class)->findOneByUuid($uuid);
    }
}
