<?php

namespace App\Twig;

use App\Admin\ReportAdmin;
use App\Entity\Report\Report;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AdminReportRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ReportAdmin $reportAdmin,
        private readonly ShowBuilderInterface $builder,
    ) {
    }

    /**
     * This was needed because sonata is not able to guess (ie. metadata) the concrete subclass of the $report in list view
     * This is not needed in sonata show view because the real $report object is used in this context.
     */
    public function generateReportSubjectAdminPath(Report $report): ?string
    {
        $routeName = 'show';
        $subject = $report->getSubject();
        $fieldDescription = $this->reportAdmin->getFieldDescriptionFactory()->create(
            $report::class, 'subject', ['route' => ['name' => $routeName], 'type' => 'string']
        );
        $fieldDescription->setAdmin($this->reportAdmin);

        $this->builder->fixFieldDescription($fieldDescription);

        if ($fieldDescription->getAssociationAdmin()
            && $fieldDescription->getAssociationAdmin()->hasRoute($routeName)
            && $fieldDescription->getAssociationAdmin()->hasAccess($routeName, $subject)
        ) {
            return $fieldDescription->getAssociationAdmin()->generateObjectUrl(
                $routeName,
                $subject, $fieldDescription->getOption('route')['parameters'] ?? []
            );
        }

        return null;
    }
}
