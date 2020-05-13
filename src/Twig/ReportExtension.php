<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReportExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('report_path', [ReportRuntime::class, 'generateReportPath']),
        ];
    }
}
