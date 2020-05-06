<?php

namespace App\Csv;

use League\Csv\AbstractCsv;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvResponseFactory
{
    private const DEFAULT_THRESHOLD = 1000;

    public function createStreamedResponse(AbstractCsv $csv): StreamedResponse
    {
        $content_callback = function () use ($csv) {
            foreach ($csv->chunk(1024) as $offset => $chunk) {
                echo $chunk;
                if (0 === $offset % self::DEFAULT_THRESHOLD) {
                    flush();
                }
            }
        };

        $response = new StreamedResponse();
        $response->headers->set('Content-Encoding', 'none');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('%s-adherents.csv', date('YmdHis'))
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->setCallback($content_callback);

        return $response;
    }
}
