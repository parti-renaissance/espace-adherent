<?php

namespace App\Command;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\SurveyQuestion;
use App\Entity\Pap\Campaign;
use App\Jecoute\SurveyQuestionTypeEnum;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Pap\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PapCampaignsCreateLocalCommand extends Command
{
    protected static $defaultName = 'app:pap:create-local-campaign';

    private ?SymfonyStyle $io = null;
    private ?EntityManagerInterface $entityManager = null;
    private ?ZoneRepository $zoneRepository = null;
    private ?VotePlaceRepository $votePlaceRepository = null;

    protected function configure()
    {
        $this
            ->setDescription('PAP: create local missing campaigns')
            ->addOption('code', null, InputOption::VALUE_REQUIRED, 'District code to filter')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of campaigns to create')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Creating missing local PAP campaigns');

        $zones = $this->zoneRepository->findZonesWithoutLocalPapCampaign(
            Zone::DISTRICT,
            $input->getOption('code')
        );

        $this->io->comment(sprintf('Found %d zones without PAP campaign', \count($zones)));

        if (empty($zones)) {
            return 0;
        }

        $questions = $this->createSurveyQuestions();

        $count = 0;
        foreach ($zones as $zone) {
            $this->io->comment(sprintf('Creating PAP campaign for zone %s', $zone->getNameCode()));

            $survey = $this->createLocalSurvey($zone, $questions);

            $campaign = $this->createLocalCampaign($zone, $survey);

            $this->entityManager->persist($survey);
            $this->entityManager->persist($campaign);
            $this->entityManager->flush();
            $this->entityManager->clear(LocalSurvey::class);
            $this->entityManager->clear(Campaign::class);

            ++$count;

            if ($count >= $input->getOption('limit')) {
                break;
            }
        }

        $this->io->success(sprintf('%d local PAP campaigns created successfully!', $count));

        return 0;
    }

    private function createSurveyQuestions(): array
    {
        $question1 = new Question(
            'Avez-vous prévu d\'aller voter les 12 et 19 juin prochains ?',
            SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE
        );
        $question1->addChoice(new Choice('Oui'));
        $question1->addChoice(new Choice('Non'));

        $question2 = new Question(
            'Si vous n\'êtes pas présent(e) le jour du scrutin, un de nos militants pourra prendre une procuration pour vous',
            SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE
        );
        $question2->addChoice(new Choice('Souhaite qu\'un militant prenne sa procuration'));
        $question2->addChoice(new Choice('Ne souhaite pas'));

        return [$question1, $question2];
    }

    private function createLocalSurvey(Zone $zone, array $questions): LocalSurvey
    {
        $survey = new LocalSurvey();
        $survey->setName(sprintf('Questionnaire local de la circonscription %s', $zone->getNameCode()));
        $survey->setPublished(true);
        $survey->setZone($zone);

        foreach ($questions as $question) {
            $survey->addQuestion(new SurveyQuestion($survey, $question));
        }

        return $survey;
    }

    private function createLocalCampaign(Zone $zone, LocalSurvey $survey): Campaign
    {
        $campaign = new Campaign(
            null,
            sprintf('Campagne de la circonscription %s', $zone->getNameCode()),
            null,
            $survey,
            100,
            new \DateTime('now'),
            new \DateTime('2022-06-19 23:59:59'),
            0,
            0,
            [$zone],
            null,
            true
        );

        $votePlaces = $this->votePlaceRepository->findForZone($zone);

        foreach ($votePlaces as $votePlace) {
            $campaign->addVotePlace($votePlace);
        }

        return $campaign;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @required
     */
    public function setVotePlaceRepository(VotePlaceRepository $votePlaceRepository): void
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }
}
