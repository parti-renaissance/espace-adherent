<?php

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Entity\Phoning\Campaign;
use App\Repository\Jecoute\DataSurveyRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhoningCampaignSurveyRepliesExporter
{
    private DataSurveyRepository $dataSurveyRepository;
    private SonataExporter $exporter;
    private int $i = 0;

    public function __construct(DataSurveyRepository $dataSurveyRepository, SonataExporter $exporter)
    {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->exporter = $exporter;
    }

    public function export(Campaign $campaign, string $format): StreamedResponse
    {
        $dataSurveysArray = new \ArrayObject($this->dataSurveyRepository->findPhoningCampaignDataSurveys($campaign, 1, null));

        return $this->exporter->getResponse(
            $format,
            sprintf(
                '%s_Replies_%s.%s',
                (new Slugify())->slugify($campaign->getTitle()),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator(
                $dataSurveysArray->getIterator(),
                function (DataSurvey $dataSurvey) use ($campaign) {
                    $row = [];
                    $row['ID'] = ++$this->i;
                    $row['Nom Prénom de l\'auteur'] = (string) $dataSurvey->getAuthor();
                    $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');
                    $row['Nom'] = $dataSurvey->getCampaignHistory()->getAdherent()->getLastName();
                    $row['Prénom'] = $dataSurvey->getCampaignHistory()->getAdherent()->getFirstName();

                    $survey = $campaign->getSurvey();

                    /** @var SurveyQuestion $surveyQuestion */
                    foreach ($survey->getQuestions() as $surveyQuestion) {
                        $questionName = $surveyQuestion->getQuestion()->getContent();
                        $row[$questionName] = '';

                        $dataAnswer = $surveyQuestion->getDataAnswersFor($surveyQuestion, $dataSurvey);

                        if (!$dataAnswer) {
                            continue;
                        }

                        if ($surveyQuestion->getQuestion()->isChoiceType()) {
                            $row[$questionName] = implode(', ', $dataAnswer->getSelectedChoices()->map(static function (Choice $choice) {
                                return $choice->getContent();
                            })->toArray());

                            continue;
                        }

                        $row[$questionName] = $dataAnswer->getTextField();
                    }

                    return $row;
                })
        );
    }
}
