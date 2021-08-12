<?php

namespace App\Command;

use App\Adherent\Command\UpdateReferentTagOnDistrictCommand;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateReferentTagsForDistrictsOnAdherentsCommand extends Command
{
    private $em;
    private $bus;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getAdherentIds() as $row) {
            $this->bus->dispatch(new UpdateReferentTagOnDistrictCommand($row['id']));
        }

        return 0;
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
