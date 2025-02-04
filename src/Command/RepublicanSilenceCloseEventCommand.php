<?php

namespace App\Command;

use App\Entity\RepublicanSilence;
use App\Event\EventCanceledHandler;
use App\Repository\Event\EventRepository;
use App\RepublicanSilence\RepublicanSilenceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:republican-silence:close-event',
    description: 'This command closes each committee or event when it matches a republican silence criteria',
)]
class RepublicanSilenceCloseEventCommand extends Command
{
    private $manager;
    private $eventRepository;
    private $eventCanceledHandler;
    private $interval;

    public function __construct(
        RepublicanSilenceManager $manager,
        EventRepository $eventRepository,
        EventCanceledHandler $eventCanceledHandler,
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->eventRepository = $eventRepository;
        $this->eventCanceledHandler = $eventCanceledHandler;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('interval', InputArgument::OPTIONAL, 'Number of minutes to remove from silence start date for closing the events [x] minutes before (default: 0)', '0')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->interval = (int) $input->getArgument('interval');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getSilences() as $silence) {
            $this->closeEvents($silence);
        }

        return self::SUCCESS;
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
        $events = $this->eventRepository->findStartedEventBetweenDatesForZones(
            (clone $silence->getBeginAt())->modify(\sprintf('-%d minutes', $this->interval)),
            $silence->getFinishAt(),
            $silence->getZones()->toArray()
        );

        foreach ($events as $event) {
            $this->eventCanceledHandler->handle($event);
        }
    }
}
