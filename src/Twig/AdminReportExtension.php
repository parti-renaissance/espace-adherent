<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminReportExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('report_subject_admin_path', [AdminReportRuntime::class, 'generateReportSubjectAdminPath']),
        ];
    }
}
