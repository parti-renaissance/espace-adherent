<?php

namespace App\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient as GoogleImageAnnotatorClient;
use Google\Cloud\Vision\V1\TextAnnotation;
use Google\Cloud\Vision\V1\WebDetection;
use Google\Protobuf\Internal\RepeatedField;
use League\Flysystem\Filesystem;

class ImageAnnotatorClient
{
    private $client;
    private $storage;

    private $textAnnotations = [];
    private $webDetections = [];

    public function __construct(string $keyFilePath, Filesystem $storage)
    {
        $this->client = new GoogleImageAnnotatorClient([
            'credentials' => $keyFilePath,
        ]);

        $this->storage = $storage;
    }

    public function getBestGuessLabels(string $filePath): RepeatedField
    {
        return $this->getWebDetection($filePath)->getBestGuessLabels();
    }

    public function getWebEntities(string $filePath): RepeatedField
    {
        return $this->getWebDetection($filePath)->getWebEntities();
    }

    public function getWebDetection(string $filePath): WebDetection
    {
        if (!array_key_exists($filePath, $this->webDetections)) {
            $response = $this->client->webDetection($this->storage->read($filePath));

            $this->webDetections[$filePath] = $response->getWebDetection();
        }

        return $this->webDetections[$filePath];
    }

    public function getFullTextAnnotation(string $filePath): TextAnnotation
    {
        if (!array_key_exists($filePath, $this->textAnnotations)) {
            $response = $this->client->documentTextDetection($this->storage->read($filePath));

            $this->textAnnotations[$filePath] = $response->getFullTextAnnotation();
        }

        return $this->textAnnotations[$filePath];
    }
}
