<?php

declare(strict_types=1);

namespace App\Vision;

class VisionHandler
{
    private $imageAnnotatorClient;

    public function __construct(ImageAnnotatorClient $imageAnnotatorClient)
    {
        $this->imageAnnotatorClient = $imageAnnotatorClient;
    }

    public function annotate(string $content): ImageAnnotations
    {
        return new ImageAnnotations(
            $this->getBestGuessLabels($content),
            $this->getWebEntities($content),
            $this->getFullTextAnnotation($content)
        );
    }

    private function getBestGuessLabels(string $content): array
    {
        $bestGuessLabels = $this->imageAnnotatorClient->getBestGuessLabels($content);

        if (!$bestGuessLabels) {
            return [];
        }

        $labels = [];
        foreach ($bestGuessLabels as $label) {
            $labels[] = $label->getLabel();
        }

        return $labels;
    }

    private function getWebEntities(string $content): array
    {
        $guessedWebEntities = $this->imageAnnotatorClient->getWebEntities($content);

        if (!$guessedWebEntities) {
            return [];
        }

        $webEntities = [];
        foreach ($guessedWebEntities as $webEntity) {
            $webEntities[] = $webEntity->getDescription();
        }

        return $webEntities;
    }

    private function getFullTextAnnotation(string $content): ?string
    {
        $annotations = $this->imageAnnotatorClient->getFullTextAnnotation($content);

        return $annotations ? $annotations->getText() : null;
    }
}
