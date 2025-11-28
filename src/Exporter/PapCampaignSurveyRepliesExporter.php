<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Entity\Pap\Campaign;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PapCampaignSurveyRepliesExporter
{
    private DataSurveyRepository $dataSurveyRepository;
    private SurveyQuestionRepository $surveyQuestionRepository;
    private SonataExporter $exporter;
    private int $i = 0;

    public function __construct(
        DataSurveyRepository $dataSurveyRepository,
        SurveyQuestionRepository $surveyQuestionRepository,
        SonataExporter $exporter,
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->surveyQuestionRepository = $surveyQuestionRepository;
        $this->exporter = $exporter;
    }

    public function export(Campaign $campaign, array $zones, string $format): StreamedResponse
    {
        $survey = $campaign->getSurvey();
        $questions = $this->surveyQuestionRepository->findForSurvey($survey);

        return $this->exporter->getResponse(
            $format,
            \sprintf(
                '%s_Replies_%s.%s',
                (new Slugify())->slugify($campaign->getTitle()),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator(
                $this->dataSurveyRepository->iterateForPapCampaignDataSurveys($campaign, $zones),
                function (array $data) use ($questions) {
                    /** @var DataSurvey $dataSurvey */
                    $dataSurvey = $data[0];
                    $papCampaignHistory = $dataSurvey->getPapCampaignHistory();

                    $row = [];
                    $row['ID'] = ++$this->i;
                    $row['Nom Prénom de l\'auteur'] = (string) $dataSurvey->getAuthor();
                    $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');
                    $row['Nom'] = $papCampaignHistory->getLastName();
                    $row['Prénom'] = $papCampaignHistory->getFirstName();
                    $row['Code postal de l\'immeuble'] = $papCampaignHistory->getBuilding()->getAddress()->getPostalCodesAsString();
                    $row['Longitude'] = $papCampaignHistory->getBuilding()->getAddress()->getLongitude();
                    $row['Latitude'] = $papCampaignHistory->getBuilding()->getAddress()->getLatitude();

                    /** @var SurveyQuestion $surveyQuestion */
                    foreach ($questions as $surveyQuestion) {
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
