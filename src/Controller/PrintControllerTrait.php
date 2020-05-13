<?php

namespace App\Controller;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Response;

trait PrintControllerTrait
{
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
        return $this->get('knp_snappy.pdf')->getOutputFromHtml($html, self::getPdfOptions());
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
