<?php

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\SurveyQuestion;
use App\Entity\Phoning\Campaign;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhoningCampaignSurveyRepliesExporter
{
    private DataSurveyRepository $dataSurveyRepository;
    private SurveyQuestionRepository $surveyQuestionRepository;
    private SonataExporter $exporter;
    private int $i = 0;

    public function __construct(
        DataSurveyRepository $dataSurveyRepository,
        SurveyQuestionRepository $surveyQuestionRepository,
        SonataExporter $exporter
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->surveyQuestionRepository = $surveyQuestionRepository;
        $this->exporter = $exporter;
    }

    public function export(Campaign $campaign, string $format): StreamedResponse
    {
        return $this->exporter->getResponse(
            $format,
            sprintf(
                '%s_Replies_%s.%s',
                (new Slugify())->slugify($campaign->getTitle()),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator(
                $this->dataSurveyRepository->iterateForPhoningCampaignDataSurveys($campaign),
                function (array $data) use ($campaign) {
                    $dataSurvey = $data[0];

                    $row = [];
                    $row['ID'] = ++$this->i;
                    $row['Nom Prénom de l\'auteur'] = (string) $dataSurvey->getAuthor();
                    $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');
                    $row['Nom'] = $dataSurvey->getCampaignHistory()->getAdherent() ? $dataSurvey->getCampaignHistory()->getAdherent()->getLastName() : null;
                    $row['Prénom'] = $dataSurvey->getCampaignHistory()->getAdherent() ? $dataSurvey->getCampaignHistory()->getAdherent()->getFirstName() : null;

                    $survey = $campaign->getSurvey();

                    /** @var SurveyQuestion $surveyQuestion */
                    foreach ($this->surveyQuestionRepository->findForSurvey($survey) as $surveyQuestion) {
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
