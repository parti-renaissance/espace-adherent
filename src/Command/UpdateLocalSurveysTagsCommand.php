<?php

namespace AppBundle\Command;

use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateLocalSurveysTagsCommand extends Command
{
    protected static $defaultName = 'app:jecoute-surveys:add-tags';

    private $em;

    private $repository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, LocalSurveyRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start updating local surveys...');
        $result = $this->repository->findAll();

        $this->io->progressStart(\count($result));

        /** @var LocalSurvey $survey */
        foreach ($result as $survey) {
            if ($survey->getAuthor()->isReferent()) {
                $tags = $survey->getAuthor()->getManagedAreaTagCodes();
            } else {
                $tags = $survey->getAuthor()->getJecouteManagedArea()->getCodes();
            }

            $survey->setTags($tags);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->em->flush();
        $this->io->success('Local surveys has been updated.');
    }
}
