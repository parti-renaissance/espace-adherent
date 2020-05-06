<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReferentTagInitializeCommitteeEvent extends Command
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
            ->setName('app:referent-tags:init-comittees-events')
            ->setDescription('Tag Committees & Events with Referent Tags')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Referent Tags import.']);

        $this->em->beginTransaction();

        try {
            $this->initializeCommitteeTags($output);
            $this->initializeEventTags($output);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $output->writeln(['', 'Referent Tags imported successfully!']);
    }

    private function initializeCommitteeTags(OutputInterface $output): void
    {
        $sql = <<<SQL
INSERT INTO committee_referent_tag (committee_id, referent_tag_id)
(
   SELECT committee.id, tag.id
   FROM committees committee
   INNER JOIN referent_tags tag ON tag.code = IF(
       committee.address_country != 'FR',
       committee.address_country,
       CASE SUBSTRING(committee.address_postal_code, 1, 2)
           -- Corsica
           WHEN '20' THEN IF(
               SUBSTRING(committee.address_postal_code, 1, 3) IN ('200', '201'),
               '2A',
               '2B'
           )
           -- Paris district
           WHEN '75' THEN committee.address_postal_code
           -- DOM
           WHEN '97' THEN IF(
               SUBSTRING(committee.address_postal_code, 1, 3) IN ('97133', '97150'),
               committee.address_postal_code,
               SUBSTRING(committee.address_postal_code, 1, 3)
           )
           -- TOM
           WHEN '98' THEN IF(
               committee.address_postal_code = '98000',
               'MC',
               SUBSTRING(committee.address_postal_code, 1, 3)
           )
           -- Regular departement code
           ELSE SUBSTRING(committee.address_postal_code, 1, 2)
       END
   )
);

-- Additional tags for Corsica (20)
INSERT INTO committee_referent_tag (committee_id, referent_tag_id)
(
    SELECT committee.id, tag.id
    FROM committees committee
    INNER JOIN referent_tags tag ON tag.code = '20'
    WHERE committee.address_country = 'FR' 
    AND SUBSTRING(committee.address_postal_code, 1, 2) = '20'
);

-- Additional tags for Paris (75)
INSERT INTO committee_referent_tag (committee_id, referent_tag_id)
(
    SELECT committee.id, tag.id
    FROM committees committee
    INNER JOIN referent_tags tag ON tag.code = '75'
    WHERE committee.address_country = 'FR' 
    AND SUBSTRING(committee.address_postal_code, 1, 2) = '75'
);
SQL;

        $output->writeln(['', 'Tagging committees.']);

        $this->em->getConnection()->exec($sql);

        $output->writeln(['', 'Committees tagged successfully.']);
    }

    private function initializeEventTags(OutputInterface $output): void
    {
        $sql = <<<SQL
INSERT INTO event_referent_tag (event_id, referent_tag_id)
(
   SELECT event.id, tag.id
   FROM events event
   INNER JOIN referent_tags tag ON tag.code = IF(
       event.address_country != 'FR',
       event.address_country,
       CASE SUBSTRING(event.address_postal_code, 1, 2)
           -- Corsica
           WHEN '20' THEN IF(
               SUBSTRING(event.address_postal_code, 1, 3) IN ('200', '201'),
               '2A',
               '2B'
           )
           -- Paris district
           WHEN '75' THEN event.address_postal_code
           -- DOM
           WHEN '97' THEN IF(
               SUBSTRING(event.address_postal_code, 1, 3) IN ('97133', '97150'),
               event.address_postal_code,
               SUBSTRING(event.address_postal_code, 1, 3)
           )
           -- TOM
           WHEN '98' THEN IF(
               event.address_postal_code = '98000',
               'MC',
               SUBSTRING(event.address_postal_code, 1, 3)
           )
           -- Regular departement code
           ELSE SUBSTRING(event.address_postal_code, 1, 2)
       END
   )
);

-- Additional tags for Corsica (20)
INSERT INTO event_referent_tag (event_id, referent_tag_id)
(
    SELECT event.id, tag.id
    FROM events event
    INNER JOIN referent_tags tag ON tag.code = '20'
    WHERE event.address_country = 'FR' 
    AND SUBSTRING(event.address_postal_code, 1, 2) = '20'
);

-- Additional tags for Paris (75)
INSERT INTO event_referent_tag (event_id, referent_tag_id)
(
    SELECT event.id, tag.id
    FROM events event
    INNER JOIN referent_tags tag ON tag.code = '75'
    WHERE event.address_country = 'FR' 
    AND SUBSTRING(event.address_postal_code, 1, 2) = '75'
);
SQL;

        $output->writeln(['', 'Tagging events.']);

        $this->em->getConnection()->exec($sql);

        $output->writeln(['', 'Events tagged successfully.']);
    }
}
