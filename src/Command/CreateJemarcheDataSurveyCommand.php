<?php

namespace App\Command;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\JemarcheDataSurvey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class CreateJemarcheDataSurveyCommand extends Command
{
    protected static $defaultName = 'app:data-survey:create-jemarche';

    private $em;
    private $dataSurveyRepository;
    private $jemarcheDataSurveyRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->dataSurveyRepository = $this->em->getRepository(DataSurvey::class);
        $this->jemarcheDataSurveyRepository = $this->em->getRepository(JemarcheDataSurvey::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create JemarcheDataSurvey.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting creation of JemarcheDataSurvey.');

        $this->io->progressStart($this->getDataSurveyCount());

        foreach ($this->getDataSurveys() as $result) {
            /* @var DataSurvey $dataSurvey */
            $dataSurvey = $result[0];

            if ($this->jemarcheDataSurveyRepository->findOneBy(['dataSurvey' => $dataSurvey])) {
                continue;
            }

            $jemarcheDS = new JemarcheDataSurvey($dataSurvey);
            $jemarcheDS->setFirstName($dataSurvey->getFirstName());
            $jemarcheDS->setLastName($dataSurvey->getLastName());
            $jemarcheDS->setAgeRange($dataSurvey->getAgeRange());
            $jemarcheDS->setGender($dataSurvey->getGender());
            $jemarcheDS->setGenderOther($dataSurvey->getGenderOther());
            $jemarcheDS->setProfession($dataSurvey->getProfession());
            $jemarcheDS->setEmailAddress($dataSurvey->getEmailAddress());
            $jemarcheDS->setPostalCode($dataSurvey->getPostalCode());
            $jemarcheDS->setDevice($dataSurvey->getDevice());
            $jemarcheDS->setAgreedToContactForJoin($dataSurvey->getAgreedToContactForJoin());
            $jemarcheDS->setAgreedToStayInContact($dataSurvey->getAgreedToStayInContact());
            $jemarcheDS->setAgreedToTreatPersonalData($dataSurvey->getAgreedToTreatPersonalData());
            $jemarcheDS->setLatitude($dataSurvey->getLatitude());
            $jemarcheDS->setLongitude($dataSurvey->getLongitude());

            $this->em->persist($jemarcheDS);
            $this->em->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('JemarcheDataSurvey have been created successfully!');

        return 0;
    }

    private function getDataSurveys(): IterableResult
    {
        return $this
            ->createDataSurveyQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getDataSurveyCount(): int
    {
        return $this
            ->createDataSurveyQueryBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createDataSurveyQueryBuilder(): QueryBuilder
    {
        return $this
            ->dataSurveyRepository
            ->createQueryBuilder('ds')
        ;
    }
}
