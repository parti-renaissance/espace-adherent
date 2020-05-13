<?php

namespace App\Report\Listener;

use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\Report\ReportableInterface;
use App\IdeasWorkshop\Events;
use App\Report\ReportManager;
use App\Report\ReportType;
use App\Repository\ReportRepository;
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

        if ($reports = $this->reportRepository->findNotResolvedByClassAndSubject($class, $object)) {
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
