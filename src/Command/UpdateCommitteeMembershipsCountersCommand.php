<?php

namespace App\Command;

use App\Repository\CommitteeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:committees:update-memberships-counters',
    description: '',
)]
class UpdateCommitteeMembershipsCountersCommand extends Command
{
    private CommitteeRepository $committeeRepository;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->committeeRepository->updateMembershipsCounters();

        return self::SUCCESS;
    }

    /** @required */
    public function setEntityManager(CommitteeRepository $committeeRepository): void
    {
        $this->committeeRepository = $committeeRepository;
    }
}
