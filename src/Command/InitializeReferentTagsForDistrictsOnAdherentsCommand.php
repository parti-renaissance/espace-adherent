<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command.
 */
class InitializeReferentTagsForDistrictsOnAdherentsCommand extends Command
{
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:referent-tags:init-for-districts')
            ->setDescription('Initialize district referent tags for adherents.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Starting tagging adherents for districts.');

        $this->em->beginTransaction();

        try {
            $sql = <<<SQL
INSERT INTO adherent_referent_tag (adherent_id, referent_tag_id)
(
   SELECT adherent.id, tag.id
   FROM adherents adherent
   INNER JOIN referent_tags tag
   INNER JOIN districts district ON district.referent_tag_id = tag.id
   INNER JOIN geo_data ON geo_data.id = district.geo_data_id
   WHERE ST_Within(ST_GeomFromText(CONCAT('POINT (', adherent.address_longitude, ' ', adherent.address_latitude, ')')), geo_data.geo_shape) = true
);
SQL;

            $this->em->getConnection()->exec($sql);
            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $this->io->success('Adherents tagged successfully!');
    }
}
