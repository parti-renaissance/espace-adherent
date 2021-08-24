<?php

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\GenderEnum;
use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyExporter
{
    /** @var JemarcheDataSurveyRepository */
    private $dataSurveyRepository;

    /** @var Exporter */
    private $exporter;

    /** @var TranslatorInterface */
    private $translator;

    /** @var int */
    private $i = 0;

    public function __construct(
        JemarcheDataSurveyRepository $dataSurveyRepository,
        SonataExporter $exporter,
        TranslatorInterface $translator
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->exporter = $exporter;
        $this->translator = $translator;
    }

    public function export(Survey $survey, string $format, bool $fromAdmin = false, array $zones = []): StreamedResponse
    {
        return $this->exporter->getResponse(
            $format,
            sprintf(
                '%s_%s_%s.%s',
                (new Slugify())->slugify($survey->getName()),
                $survey->getId(),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator($this->dataSurveyRepository->iterateForSurvey($survey, $zones), function (array $data) use ($survey, $fromAdmin) {
                /** @var JemarcheDataSurvey $jemarcheDataSurvey */
                $jemarcheDataSurvey = $data[0];
                $dataSurvey = $jemarcheDataSurvey->getDataSurvey();

                $allowPersonalData = $fromAdmin || $jemarcheDataSurvey->getAgreedToTreatPersonalData();

                $row = [];
                $row['ID'] = ++$this->i;

                $author = $dataSurvey->getAuthor();
                if ($fromAdmin) {
                    $row['Adherent ID'] = $author ? $author->getId() : null;
                }

                $row['Nom Prénom de l\'auteur'] = (string) $author;
                $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');
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
