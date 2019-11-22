<?php

namespace AppBundle\Command;

use AppBundle\Entity\ProgrammaticFoundation\Approach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetProgrammaticFoundationCommand extends Command
{
    protected static $defaultName = 'app:programmatic-foundation:reset';

    private $em;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Reset Programmatic Foundation');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->section('Resetting programmatic foundation.');

        $approaches = $this->em->getRepository(Approach::class)->findAll();

        foreach ($approaches as $approach) {
            $this->em->remove($approach);
        }

        $this->em->flush();

        $this->io->success('Programmatic foundation resetted successfully!');
    }
}
