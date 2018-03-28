<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReportRuntime
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
