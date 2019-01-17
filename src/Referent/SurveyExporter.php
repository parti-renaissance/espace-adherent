<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Jecoute\Survey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Survey[] $surveys
     */
    public function exportAsJson(array $surveys): string
    {
        $data = [];
        $i = 0;

        foreach ($surveys as $survey) {
            $data[] = [
                'id' => $survey->getId(),
                'name' => $survey->getName(),
                'city' => $survey->getCity(),
                'questionsCount' => $survey->questionsCount(),
                'createdAt' => $survey->getCreatedAt()->format('d/m/Y'),
                'author' => $survey->getAuthor()->getFullName(),
                'publish' => $this->getPublishAction($survey->isPublished()),
                'edit' => [
                    'label' => "<span id='survey-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate('app_referent_survey_edit', ['uuid' => $survey->getUuid()]),
                ],
                'stats' => [
                    'label' => "<span id='survey-stats-$i' class='btn btn--default'><i class='fa fa-bar-chart'></i></span>",
                    'url' => $this->urlGenerator->generate('app_referent_survey_stats', ['uuid' => $survey->getUuid()]),
                    ],
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
