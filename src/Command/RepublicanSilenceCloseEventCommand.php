<?php

namespace App\Command;

use App\Entity\RepublicanSilence;
use App\Event\EventCanceledHandler;
use App\Repository\CitizenActionRepository;
use App\Repository\EventRepository;
use App\RepublicanSilence\RepublicanSilenceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepublicanSilenceCloseEventCommand extends Command
{
    protected static $defaultName = 'app:republican-silence:close-event';

    private $manager;
    private $eventRepository;
    private $actionRepository;
    private $eventCanceledHandler;
    private $interval;

    public function __construct(
        RepublicanSilenceManager $manager,
        EventRepository $eventRepository,
        CitizenActionRepository $actionRepository,
        EventCanceledHandler $eventCanceledHandler
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->eventRepository = $eventRepository;
        $this->actionRepository = $actionRepository;
        $this->eventCanceledHandler = $eventCanceledHandler;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command closes each committee event or citizen action when it matches a republican silence criteria')
            ->addArgument('interval', InputArgument::OPTIONAL, 'Number of minutes to remove from silence start date for closing the events [x] minutes before (default: 0)', 0)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->interval = (int) $input->getArgument('interval');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSilences() as $silence) {
            $this->closeEvents($silence);
            $this->closeActions($silence);
        }
    }

    /**
     * @return RepublicanSilence[]
     */
    private function getSilences(): iterable
    {
        return $this->manager->getRepublicanSilencesFromDate(new \DateTime());
    }

    private function closeEvents(RepublicanSilence $silence): void
    {
        $events = $this->eventRepository->findStartedEventBetweenDatesForTags(
            (clone $silence->getBeginAt())->modify(sprintf('-%d minutes', $this->interval)),
            $silence->getFinishAt(),
            $silence->getReferentTags()->toArray()
        );

        foreach ($events as $event) {
            $this->eventCanceledHandler->handle($event);
        }
    }

    private function closeActions(RepublicanSilence $silence): void
    {
        $actions = $this->actionRepository->findStartedEventBetweenDatesForTags(
            (clone $silence->getBeginAt())->modify(sprintf('-%d minutes', $this->interval)),
            $silence->getFinishAt(),
            $silence->getReferentTags()->toArray()
        );

        foreach ($actions as $action) {
            $this->eventCanceledHandler->handle($action);
        }
    }
}
