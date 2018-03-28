<?php

namespace AppBundle\Twig;

use AppBundle\Admin\ReportAdmin;
use AppBundle\Entity\Report;
use Sonata\DoctrineORMAdminBundle\Builder\ShowBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReportExtension extends AbstractExtension
{
    private $reportAdmin;
    private $builder;

    public function __construct(ReportAdmin $reportAdmin, ShowBuilder $builder)
    {
        $this->reportAdmin = $reportAdmin;
        $this->builder = $builder;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('report_subject_admin_path', [$this, 'generateReportSubjectAdminPath']),
        ];
    }

    /**
     * This was needed because sonata is not able to guess (ie. metadata) the concrete subclass of the $report in list view
     * This is not needed in sonata show view because the real $report object is used in this context.
     */
    public function generateReportSubjectAdminPath(Report $report): ?string
    {
        $routeName = 'show';
        $subject = $report->getSubject();
        $fieldDescription = $this->reportAdmin->getModelManager()->getNewFieldDescriptionInstance(
            get_class($report), 'subject', ['route' => ['name' => $routeName]]
        );

        $this->builder->fixFieldDescription($this->reportAdmin, $fieldDescription);

        if ($fieldDescription->getAssociationAdmin()
            && $fieldDescription->getAssociationAdmin()->hasRoute($routeName)
            && $fieldDescription->getAssociationAdmin()->hasAccess($routeName, $subject)
        ) {
            return $fieldDescription->getAssociationAdmin()->generateObjectUrl(
                $routeName,
                $subject, $fieldDescription->getOption('route')['parameters']
            );
        }

        return null;
    }
}
