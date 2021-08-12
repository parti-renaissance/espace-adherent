<?php

namespace App\Command;

use App\Entity\RepublicanSilence;
use App\Event\EventCanceledHandler;
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
    private $eventCanceledHandler;
    private $interval;

    public function __construct(
        RepublicanSilenceManager $manager,
        EventRepository $eventRepository,
        EventCanceledHandler $eventCanceledHandler
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->eventRepository = $eventRepository;
        $this->eventCanceledHandler = $eventCanceledHandler;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command closes each committee or event when it matches a republican silence criteria')
            ->addArgument('interval', InputArgument::OPTIONAL, 'Number of minutes to remove from silence start date for closing the events [x] minutes before (default: 0)', '0')
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
        }

        return 0;
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
}
