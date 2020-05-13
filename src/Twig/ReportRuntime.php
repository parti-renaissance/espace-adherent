<?php

namespace App\Twig;

use App\Entity\Report\ReportableInterface;
use App\Report\ReportType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ReportRuntime implements RuntimeExtensionInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function generateReportPath(ReportableInterface $subject, string $redirectUrl): string
    {
        return $this->urlGenerator->generate('app_report', [
            'type' => ReportType::getEntityUriType($subject),
            'uuid' => $subject->getUuid()->toString(),
            'redirectUrl' => $redirectUrl,
        ]);
    }
}
