<?php


namespace App\Vision;

use Google\Cloud\Vision\V1\WebDetection;

class VisionHandler
{
    private const IDENTITY_DOCUMENT_LABEL = 'Identity document';
    private const NATIONAL_IDENTITY_CARD_LABEL = 'National identity card';
    private const FRENCH_IDENTITY_CARD_LABEL = 'carte d identité française';

    private $imageAnnotatorClient;

    public function __construct(ImageAnnotatorClient $imageAnnotatorClient)
    {
        $this->imageAnnotatorClient = $imageAnnotatorClient;
    }

    public function isFrenchNationalIdentityCard(string $filePath): bool
    {
        $labels = $this->imageAnnotatorClient->getBestGuessLabels($filePath);

        if (!$labels->offsetExists(0)) {
            return false;
        }

        dump($labels->offsetGet(0)->getLabel());
        throw new \Exception('test');

        if (self::FRENCH_IDENTITY_CARD_LABEL !== $labels->offsetGet(0)->getLabel()) {
            return false;
        }

        $webEntities = $this->imageAnnotatorClient->getWebEntities($filePath);

        if (0 >= count($webEntities)) {
            return false;
        }

        if (!$webEntities->offsetExists(0) || !$webEntities->offsetExists(1)) {
            return false;
        }

        if (
            self::IDENTITY_DOCUMENT_LABEL !== $webEntities->offsetGet(0)->getDescription()
            || self::NATIONAL_IDENTITY_CARD_LABEL !== $webEntities->offsetGet(1)->getDescription()
        ) {
            return false;
        }

        return true;
    }
}