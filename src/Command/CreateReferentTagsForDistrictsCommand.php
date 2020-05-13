<?php

namespace App\Command;

use App\Entity\District;
use App\Entity\ReferentTag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command.
 */
class CreateReferentTagsForDistrictsCommand extends Command
{
    private $em;
    private $districtRepository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->districtRepository = $this->em->getRepository(District::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:referent-tags:for-districts')
            ->setDescription('Create or update Referent Tags for Districts.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Starting Referent Tags creation or update for Districts.');

        foreach ($this->districtRepository->findAll() as $district) {
            if ($tag = $district->getReferentTag()) {
                $tag->setName($district->getFullName());
                $tag->setCode('CIRCO_'.$district->getCode());
            } else {
                $tag = new ReferentTag($district->getFullName(), 'CIRCO_'.$district->getCode());
                $this->em->persist($tag);
            }

            $district->setReferentTag($tag);
        }

        $this->em->flush();

        $this->io->success('Referent Tags creation and update from Districts finished successfully!');
    }
}
