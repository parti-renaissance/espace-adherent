<?php

namespace App\Command;

use App\Repository\CommitteeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommitteeMembershipsCountersCommand extends Command
{
    protected static $defaultName = 'app:committees:update-memberships-counters';

    private CommitteeRepository $committeeRepository;

    protected function execute(InputInterface $input, OutputInterface $output)
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
