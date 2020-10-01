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
        $labels = [];
        foreach ($this->imageAnnotatorClient->getBestGuessLabels($filePath) as $label) {
            $labels[] = $label->getLabel();
        }

        return $labels;
    }

    private function getWebEntities(string $filePath): array
    {
        $webEntities = [];
        foreach ($this->imageAnnotatorClient->getWebEntities($filePath) as $webEntity) {
            $webEntities[] = $webEntity->getDescription();
        }

        return $webEntities;
    }

    private function getFullTextAnnotation(string $filePath): string
    {
        return $this->imageAnnotatorClient->getFullTextAnnotation($filePath)->getText();
    }
}
