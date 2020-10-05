<?php

namespace App\Vision;

class VisionHandler
{
    private $imageAnnotatorClient;

    public function __construct(ImageAnnotatorClient $imageAnnotatorClient)
    {
        $this->imageAnnotatorClient = $imageAnnotatorClient;
    }

    public function annotate(string $filePath): ImageAnnotations
    {
        return new ImageAnnotations(
            $this->getBestGuessLabels($filePath),
            $this->getWebEntities($filePath),
            $this->getFullTextAnnotation($filePath)
        );
    }

    private function getBestGuessLabels(string $filePath): array
    {
        $bestGuessLabels = $this->imageAnnotatorClient->getBestGuessLabels($filePath);

        if (!$bestGuessLabels) {
            return [];
        }

        $labels = [];
        foreach ($bestGuessLabels as $label) {
            $labels[] = $label->getLabel();
        }

        return $labels;
    }

    private function getWebEntities(string $filePath): array
    {
        $webEntities = $this->imageAnnotatorClient->getWebEntities($filePath);

        if (!$webEntities) {
            return [];
        }

        $webEntities = [];
        foreach ($webEntities as $webEntity) {
            $webEntities[] = $webEntity->getDescription();
        }

        return $webEntities;
    }

    private function getFullTextAnnotation(string $filePath): ?string
    {
        $annotations = $this->imageAnnotatorClient->getFullTextAnnotation($filePath);

        return $annotations ? $annotations->getText() : null;
    }
}
