<?php

namespace AppBundle\Command;

use AppBundle\Entity\Jecoute\SurveyQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SurveyQuestionUpdateCommand extends Command
{
    private const BATCH_SIZE = 50;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:jecoute:update-questions-uuid')
            ->setDescription('Generates the uuids for the questions already in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting...']);

        $this->em->beginTransaction();

        try {
            $this->updateUuids($output);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $output->writeln(['', 'Done.']);
    }

    private function updateUuids(OutputInterface $output): void
    {
        $progressBar = new ProgressBar($output, $this->countSurveyQuestions());

        $batchCount = 0;
        $updatedCount = 0;

        foreach ($this->getSurveyQuestions() as $result) {
            /* @var SurveyQuestion $surveyQuestion */
            $surveyQuestion = $result[0];

            if (!$surveyQuestion->hasUuid()) {
                $surveyQuestion->setUuid(Uuid::uuid4());

                ++$updatedCount;
            }

            $progressBar->advance();

            if (0 === ($batchCount % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
            }

            ++$batchCount;
        }

        $this->em->flush();
        $progressBar->finish();

        $output->writeln(['', "$updatedCount SurveyQuestion updated."]);
    }

    private function getSurveyQuestions(): IterableResult
    {
        return $this
            ->createSurveyQuestionQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function countSurveyQuestions(): int
    {
        return $this
            ->createSurveyQuestionQueryBuilder()
            ->select('COUNT(surveyQuestion)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createSurveyQuestionQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(SurveyQuestion::class)
            ->createQueryBuilder('surveyQuestion')
        ;
    }
}
