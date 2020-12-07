<?php

namespace App\Exporter;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\GenderEnum;
use App\Repository\Jecoute\DataSurveyRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\TranslatorInterface;

class SurveyExporter
{
    /** @var DataSurveyRepository */
    private $dataSurveyRepository;

    /** @var Exporter */
    private $exporter;

    /** @var TranslatorInterface */
    private $translator;

    /** @var int */
    private $i = 0;

    public function __construct(
        DataSurveyRepository $dataSurveyRepository,
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
                /** @var DataSurvey $dataSurvey */
                $dataSurvey = $data[0];

                $allowPersonalData = $fromAdmin || $dataSurvey->getAgreedToTreatPersonalData();

                $row = [];
                $row['ID'] = ++$this->i;

                if ($fromAdmin) {
                    $row['Adherent ID'] = $dataSurvey->getAuthor()->getId();
                }

                $row['Nom Prénom de l\'auteur'] = $dataSurvey->getAuthor();
                $row['Posté le'] = $dataSurvey->getPostedAt()->format('d/m/Y H:i:s');
                $row['Nom'] = $allowPersonalData ? $dataSurvey->getFirstName() : null;
                $row['Prénom'] = $allowPersonalData ? $dataSurvey->getLastName() : null;

                if ($fromAdmin) {
                    $row['Email'] = $dataSurvey->getEmailAddress();
                    $row['Accepte d\'être contacté'] = (int) $dataSurvey->getAgreedToStayInContact();
                    $row['Accepte d\'être invité à adhérer'] = (int) $dataSurvey->getAgreedToContactForJoin();
                }

                $row['Code postal'] = $allowPersonalData ? $dataSurvey->getPostalCode() : null;
                $row['Tranche d\'age'] = $allowPersonalData && $dataSurvey->getAgeRange() ? $this->translator->trans('survey.age_range.'.$dataSurvey->getAgeRange()) : null;
                $row['Genre'] = $allowPersonalData && $dataSurvey->getGender() ? (GenderEnum::OTHER === $dataSurvey->getGender() ? $dataSurvey->getGenderOther() : $this->translator->trans('common.'.$dataSurvey->getGender())) : null;

                if ($fromAdmin) {
                    $row['Accepte que ses données soient traitées'] = (int) $dataSurvey->getAgreedToTreatPersonalData();
                }

                $row['Profession'] = $allowPersonalData && $dataSurvey->getProfession() ? $dataSurvey->getProfession() : null;

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
