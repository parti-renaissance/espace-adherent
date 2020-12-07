<?php

namespace App\Controller;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

trait PrintControllerTrait
{
    /** @var Pdf */
    private $pdfService;

    /** @required */
    public function setPdfService(Pdf $pdfService): void
    {
        $this->pdfService = $pdfService;
    }

    public function getPdfResponse(string $template, array $params, string $pdfName): Response
    {
        return new PdfResponse($this->renderPdfResponse($template, $params), $pdfName);
    }

    public function renderPdfResponse(string $template, array $params): string
    {
        $html = $this->renderView($template, $params);

        return $this->getPdfForResponse($html);
    }

    public function getPdfForResponse(string $html): string
    {
        return $this->pdfService->getOutputFromHtml($html, self::getPdfOptions());
    }

    private static function getPdfOptions(): array
    {
        return [
            'footer-center' => 'Page [page]/[toPage]',
            'footer-font-size' => 10,
            'margin-top' => 10,
            'margin-bottom' => 15,
            'margin-left' => 15,
            'margin-right' => 15,
        ];
    }
}
