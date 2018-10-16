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
                'questionsCount' => $survey->questionsCount(),
                'createdAt' => $survey->getCreatedAt()->format('d/m/Y'),
                'creator' => $survey->getCreator()->getPartialName(),
                'edit' => [
                    'label' => "<span id='survey-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate(
                        'app_referent_survey_edit', ['uuid' => $survey->getUuid()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ],
            ];
            ++$i;
        }

        return \GuzzleHttp\json_encode($data);
    }
}
