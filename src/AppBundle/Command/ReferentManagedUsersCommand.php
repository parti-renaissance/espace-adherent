<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Producer\ReferentManagedUsersDumperProducer;
use AppBundle\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReferentManagedUsersCommand extends ContainerAwareCommand
{
    private static $types = [
        'all',
        'subscribers',
        'adherents',
        'non_followers',
        'followers',
        'hosts',
        'serialized',
    ];

    protected function configure()
    {
        $this
            ->setName('app:referent:dump')
            ->setDescription('Produce messages to update referents users dumps')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting on '.date('d/m/Y H:i:s'));

        /** @var ReferentManagedUsersDumperProducer $producer */
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.referent_managed_users_dumper_producer');

        /** @var AdherentRepository $repository */
        $repository = $this->getContainer()->get('doctrine')->getRepository(Adherent::class);

        /** @var Adherent[] $referents */
        $referents = $repository->findReferents();

        foreach ($referents as $referent) {
            $output->write('Publishing '.$referent->getEmailAddress().'... ');

            foreach (self::$types as $type) {
                $producer->scheduleDump($referent, $type);
            }

            $output->writeln('Done');
        }
    }
}
