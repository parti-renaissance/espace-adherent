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

    public function export(Survey $survey, string $format, bool $allowedOnly = true): StreamedResponse
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
            new IteratorCallbackSourceIterator($this->dataSurveyRepository->iterateForSurvey($survey), function (array $data) use ($survey, $allowedOnly) {
                /** @var DataSurvey $dataSurvey */
                $dataSurvey = $data[0];

                $allowPersonalData = !$allowedOnly || $dataSurvey->getAgreedToTreatPersonalData();

                $row = [
                    'ID' => ++$this->i,
                    'Adherent ID' => $dataSurvey->getAuthor()->getId(),
                    'Nom Prénom de l\'auteur' => $dataSurvey->getAuthor(),
                    'Posté le' => $dataSurvey->getPostedAt()->format('d/m/Y H:i:s'),
                    'Nom' => $allowPersonalData ? $dataSurvey->getFirstName() : null,
                    'Prénom' => $allowPersonalData ? $dataSurvey->getLastName() : null,
                    'Email' => $allowPersonalData ? $dataSurvey->getEmailAddress() : null,
                    'Accepte d\'être contacté' => (int) $dataSurvey->getAgreedToStayInContact(),
                    'Accepte d\'être invité à adhérer' => (int) $dataSurvey->getAgreedToContactForJoin(),
                    'Code postal' => $allowPersonalData ? $dataSurvey->getPostalCode() : null,
                    'Tranche d\'age' => $allowPersonalData && $dataSurvey->getAgeRange() ? $this->translator->trans('survey.age_range.'.$dataSurvey->getAgeRange()) : null,
                    'Genre' => $allowPersonalData && $dataSurvey->getGender() ? (GenderEnum::OTHER === $dataSurvey->getGender() ? $dataSurvey->getGenderOther() : $this->translator->trans('common.'.$dataSurvey->getGender())) : null,
                    'Accepte que ses données soient traitées' => (int) $dataSurvey->getAgreedToTreatPersonalData(),
                    'Profession' => $allowPersonalData && $dataSurvey->getProfession() ? $dataSurvey->getProfession() : null,
                ];

                /** @var SurveyQuestion $surveyQuestion */
                foreach ($survey->getQuestions() as $surveyQuestion) {
                    foreach ($surveyQuestion->getDataAnswersForDataSurvey($dataSurvey) as $dataAnswer) {
                        if ($surveyQuestion->getQuestion()->isChoiceType()) {
                            $row[$surveyQuestion->getQuestion()->getContent()] = implode(', ', $dataAnswer->getSelectedChoices()->map(static function (Choice $choice) {
                                return $choice->getContent();
                            })->toArray());

                            continue;
                        }

                        $row[$surveyQuestion->getQuestion()->getContent()] = $dataAnswer->getTextField();
                    }
                }

                return $row;
            })
        );
    }
}
