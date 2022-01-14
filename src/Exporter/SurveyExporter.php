<?php

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\GenderEnum;
use App\Jecoute\ProfessionEnum;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyExporter
{
    private DataSurveyRepository $dataSurveyRepository;
    private SurveyQuestionRepository $surveyQuestionRepository;
    private Exporter $exporter;
    private TranslatorInterface $translator;
    private $i = 0;

    public function __construct(
        DataSurveyRepository $dataSurveyRepository,
        SurveyQuestionRepository $surveyQuestionRepository,
        SonataExporter $exporter,
        TranslatorInterface $translator
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->surveyQuestionRepository = $surveyQuestionRepository;
        $this->exporter = $exporter;
        $this->translator = $translator;
    }

    public function export(
        Survey $survey,
        string $format,
        bool $fromAdmin = false,
        array $zones = [],
        array $departmentCodes = []
    ): StreamedResponse {
        $questions = $this->surveyQuestionRepository->findForSurvey($survey);

        return $this->exporter->getResponse(
            $format,
            sprintf(
                '%s_%s_%s.%s',
                (new Slugify())->slugify($survey->getName()),
                $survey->getId(),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator($this->dataSurveyRepository->iterateForSurvey($survey, $zones, $departmentCodes), function (array $data) use ($fromAdmin, $questions) {
                /** @var DataSurvey $dataSurvey */
                $dataSurvey = $data[0];
                $jemarcheDataSurvey = $dataSurvey->getJemarcheDataSurvey();
                $phoningCampaignHistory = $dataSurvey->getPhoningCampaignHistory();
                $papCampaignHistory = $dataSurvey->getPapCampaignHistory();

                $row = [];
                $row['ID'] = ++$this->i;

                $author = $dataSurvey->getAuthor();
                if ($fromAdmin) {
                    $row['Adherent ID'] = $author ? $author->getId() : null;
                }

                $row['Nom Prénom de l\'auteur'] = (string) $author;
                $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');

                if ($phoningCampaignHistory) {
                    $adherent = $phoningCampaignHistory->getAdherent();
                    $row['Nom'] = $adherent->getLastName();
                    $row['Prénom'] = $adherent->getFirstName();

                    if ($fromAdmin) {
                        $row['Email'] = $adherent->getEmailAddress();
                        $row['Accepte d\'être contacté'] = null;
                        $row['Accepte d\'être invité à adhérer'] = null;
                    }

                    $row['Code postal'] = $adherent->getPostalCode();
                    $row['Tranche d\'age'] = null;
                    $row['Genre'] = $adherent->getGender()
                        ? (GenderEnum::OTHER === $adherent->getGender()
                            ? $adherent->getGenderOther()
                            : ($this->translator->trans('common.'.$adherent->getGender())))
                        : null;

                    if ($fromAdmin) {
                        $row['Accepte que ses données soient traitées'] = null;
                    }

                    $row['Profession'] = $adherent->getPosition() ? $this->translator->trans('adherent.activity_position.'.$adherent->getPosition()) : null;
                } elseif ($papCampaignHistory) {
                    $row['Nom'] = $papCampaignHistory->getLastName();
                    $row['Prénom'] = $papCampaignHistory->getFirstName();

                    if ($fromAdmin) {
                        $row['Email'] = $papCampaignHistory->getEmailAddress();
                        $row['Accepte d\'être contacté'] = $papCampaignHistory->isToContact();
                        $row['Accepte d\'être invité à adhérer'] = null;
                    }

                    $row['Code postal'] = $papCampaignHistory->getVoterPostalCode();
                    $row['Tranche d\'age'] = $papCampaignHistory->getAgeRange();
                    $row['Genre'] = $papCampaignHistory->getGender() ? $this->translator->trans('common.'.$papCampaignHistory->getGender()) : null;

                    if ($fromAdmin) {
                        $row['Accepte que ses données soient traitées'] = null;
                    }

                    $row['Profession'] = $papCampaignHistory->getProfession() ? ProfessionEnum::choices()[$papCampaignHistory->getProfession()] : null;
                } elseif ($jemarcheDataSurvey) {
                    $allowPersonalData = $fromAdmin || $jemarcheDataSurvey->getAgreedToTreatPersonalData();
                    $row['Nom'] = $allowPersonalData ? $jemarcheDataSurvey->getFirstName() : null;
                    $row['Prénom'] = $allowPersonalData ? $jemarcheDataSurvey->getLastName() : null;

                    if ($fromAdmin) {
                        $row['Email'] = $jemarcheDataSurvey->getEmailAddress();
                        $row['Accepte d\'être contacté'] = (int) $jemarcheDataSurvey->getAgreedToStayInContact();
                        $row['Accepte d\'être invité à adhérer'] = (int) $jemarcheDataSurvey->getAgreedToContactForJoin();
                    }

                    $row['Code postal'] = $allowPersonalData ? $jemarcheDataSurvey->getPostalCode() : null;
                    $row['Tranche d\'age'] = $allowPersonalData && $jemarcheDataSurvey->getAgeRange() ? $this->translator->trans('survey.age_range.'.$jemarcheDataSurvey->getAgeRange()) : null;
                    $row['Genre'] = $allowPersonalData && $jemarcheDataSurvey->getGender() ? (GenderEnum::OTHER === $jemarcheDataSurvey->getGender() ? $jemarcheDataSurvey->getGenderOther() : $this->translator->trans('common.'.$jemarcheDataSurvey->getGender())) : null;

                    if ($fromAdmin) {
                        $row['Accepte que ses données soient traitées'] = (int) $jemarcheDataSurvey->getAgreedToTreatPersonalData();
                    }

                    $row['Profession'] = $allowPersonalData && $jemarcheDataSurvey->getProfession() ? $jemarcheDataSurvey->getProfession() : null;
                } else {
                    $row['Nom'] = $row['Prénom'] = $row['Code postal'] = '';
                    $row['Tranche d\'age'] = $row['Genre'] = $row['Profession'] = '';
                    if ($fromAdmin) {
                        $row['Email'] = $row['Accepte d\'être contacté'] = '';
                        $row['Accepte d\'être invité à adhérer'] = $row['Accepte que ses données soient traitées'] = '';
                    }
                }

                /** @var SurveyQuestion $surveyQuestion */
                foreach ($questions as $surveyQuestion) {
                    $question = $surveyQuestion->getQuestion();
                    $questionName = $question->getContent();
                    $row[$questionName] = '';

                    $dataAnswer = $surveyQuestion->getDataAnswersFor($surveyQuestion, $dataSurvey);

                    if (!$dataAnswer) {
                        continue;
                    }

                    if ($question->isChoiceType()) {
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
