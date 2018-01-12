<?php

namespace AppBundle\Controller;

trait PrintControllerTrait
{
    public function getPdfForResponse(string $html): string
    {
        return $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $this->getPdfOptions());
    }

    private function getPdfOptions(): array
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
