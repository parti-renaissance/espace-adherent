<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Action\Action;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use App\Repository\Action\ActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:action:notify',
    description: 'Send notification push for actions (on time or 1h before)'
)]
class ActionNotifyCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ActionRepository $actionRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getActionsToNotify(true) as $action) {
            $this->notifyAction($action, true);
        }

        foreach ($this->getActionsToNotify(false) as $action) {
            $this->notifyAction($action, false);
        }

        return self::SUCCESS;
    }

    private function notifyAction(Action $action, bool $firstNotification): void
    {
        $this->messageBus->dispatch(new NotifyForActionCommand(
            $action->getUuid(),
            $firstNotification ? NotifyForActionCommand::EVENT_FIRST_NOTIFICATION : NotifyForActionCommand::EVENT_SECOND_NOTIFICATION
        ));

        if ($firstNotification) {
            $action->notifiedAtFirstNotification = new \DateTime();
        } else {
            $action->notifiedAtSecondNotification = new \DateTime();
        }

        $this->entityManager->flush();
    }

    private function getActionsToNotify(bool $firstNotification): array
    {
        $queryBuilder = $this->actionRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere('a.date >= :from_date AND a.date <= :to_date')
            ->setParameters([
                'status' => Action::STATUS_SCHEDULED,
                'from_date' => new \DateTime('-15 min'),
                'to_date' => new \DateTime(),
            ])
        ;

        if ($firstNotification) {
            $queryBuilder
                ->setParameter('to_date', new \DateTime('+1 hour'))
                ->andWhere('a.notifiedAtFirstNotification IS NULL')
            ;
        } else {
            $queryBuilder->andWhere('a.notifiedAtSecondNotification IS NULL');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
