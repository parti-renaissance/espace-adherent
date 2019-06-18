<?php

namespace AppBundle\Jecoute;

use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\NationalSurvey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param LocalSurvey[] $surveys
     */
    public function exportLocalSurveysAsJson(array $surveys, string $spaceName): string
    {
        $data = [];
        $i = 0;

        /** @var LocalSurvey $survey */
        foreach ($surveys as $survey) {
            $data[] = [
                'id' => $survey->getId(),
                'name' => $survey->getName(),
                'city' => $survey->getCity(),
                'questionsCount' => $survey->questionsCount(),
                'createdAt' => $survey->getCreatedAt()->format('d/m/Y'),
                'author' => $survey->getAuthor()->getFullName(),
                'edit' => [
                    'label' => "<span id='survey-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate("app_jecoute_{$spaceName}_local_survey_edit", ['uuid' => $survey->getUuid()]),
                ],
                'stats' => [
                    'label' => "<span id='survey-stats-$i' class='btn btn--default'><i class='fa fa-bar-chart'></i></span>",
                    'url' => $this->urlGenerator->generate("app_jecoute_{$spaceName}_survey_stats", ['uuid' => $survey->getUuid()]),
                ],
                'duplicate' => [
                    'label' => "<span class='btn btn--default'><i class='fa fa-copy'></i></span>",
                    'url' => $this->urlGenerator->generate("app_jecoute_{$spaceName}_local_survey_duplicate", ['uuid' => $survey->getUuid()]),
                ],
                'publish' => $this->getPublishAction($survey->isPublished()),
            ];
            ++$i;
        }

        return \GuzzleHttp\json_encode($data);
    }

    /**
     * @param NationalSurvey[] $surveys
     */
    public function exportNationalSurveysAsJson(array $surveys, string $spaceName): string
    {
        $data = [];
        $i = 0;

        /** @var NationalSurvey $survey */
        foreach ($surveys as $survey) {
            $data[] = [
                'id' => $survey->getId(),
                'name' => $survey->getName(),
                'questionsCount' => $survey->questionsCount(),
                'createdAt' => $survey->getCreatedAt()->format('d/m/Y'),
                'stats' => [
                    'label' => "<span id='survey-stats-$i' class='btn btn--default'><i class='fa fa-bar-chart'></i></span>",
                    'url' => $this->urlGenerator->generate("app_jecoute_{$spaceName}_survey_stats", ['uuid' => $survey->getUuid()]),
                ],
                'show' => [
                    'label' => "<span id='survey-edit-$i' class='btn btn--default'><i class='fa fa-search-plus'></i></span>",
                    'url' => $this->urlGenerator->generate("app_jecoute_{$spaceName}_national_survey_show", ['uuid' => $survey->getUuid()]),
                ],
                'publish' => $this->getPublishAction($survey->isPublished()),
            ];
            ++$i;
        }

        return \GuzzleHttp\json_encode($data);
    }

    private function getPublishAction(bool $isPublished): string
    {
        if ($isPublished) {
            return "<i class='fa fa-check-square text--medium text--blue--soft'></i>";
        }

        return "<i class='fa fa-square text--medium text--light-gray'></i>";
    }
}
