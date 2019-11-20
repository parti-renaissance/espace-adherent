<?php

namespace AppBundle\Command;

use AppBundle\Adherent\Command\UpdateReferentTagOnDistrictCommand;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateReferentTagsForDistrictsOnAdherentsCommand extends Command
{
    private $em;
    private $bus;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:referent-tags:update-for-districts')
            ->setDescription('Update district referent tags for adherents.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getAdherentIds() as $row) {
            $this->bus->dispatch(new UpdateReferentTagOnDistrictCommand($row['id']));
        }
    }

    private function getAdherentIds(): array
    {
        return $this->em->createQueryBuilder()
            ->select('a.id')
            ->from(Adherent::class, 'a')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
