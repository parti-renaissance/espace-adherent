<?php

namespace AppBundle\Report\Listener;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\IdeasWorkshop\Events;
use AppBundle\Report\ReportManager;
use AppBundle\Report\ReportType;
use AppBundle\Repository\ReportRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EnableSubjectForResolvingReportsListener implements EventSubscriberInterface
{
    private $reportRepository;
    private $reportManager;

    public function __construct(ReportRepository $reportRepository, ReportManager $reportManager)
    {
        $this->reportRepository = $reportRepository;
        $this->reportManager = $reportManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::THREAD_ENABLE => 'resolveReports',
            Events::THREAD_DISABLE => 'resolveReports',
            Events::THREAD_COMMENT_ENABLE => 'resolveReports',
            Events::THREAD_COMMENT_DISABLE => 'resolveReports',
            Events::IDEA_ENABLE => 'resolveReports',
            Events::IDEA_DISABLE => 'resolveReports',
        ];
    }

    public function resolveReports(GenericEvent $event): void
    {
        /** @var ReportableInterface $object */
        $object = $event->getSubject();
        switch (\get_class($object)) {
            case Idea::class:
                $class = ReportType::LIST[ReportType::IDEAS_WORKSHOP_IDEA];
                break;
            case Thread::class:
                $class = ReportType::LIST[ReportType::IDEAS_WORKSHOP_THREAD];
                break;
            case ThreadComment::class:
                $class = ReportType::LIST[ReportType::IDEAS_WORKSHOP_THREAD_COMMENT];
                break;
            default: throw new BadRequestHttpException('The class of reported object is not known.');
        }

        if ($reports = $this->reportRepository->findByClassAndSubject($class, $object)) {
            try {
                foreach ($reports as $report) {
                    $this->reportManager->resolve($report);
                }
            } catch (\LogicException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }
        }
    }
}
