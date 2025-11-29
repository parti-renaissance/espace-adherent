<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CommitteeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

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

    #[Required]
    public function setEntityManager(CommitteeRepository $committeeRepository): void
    {
        $this->committeeRepository = $committeeRepository;
    }
}
