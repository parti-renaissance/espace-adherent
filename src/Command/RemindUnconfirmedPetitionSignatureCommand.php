<?php

declare(strict_types=1);

namespace App\Command;

use App\Renaissance\Petition\SignatureManager;
use App\Repository\PetitionSignatureRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:petition:remind-signature',
    description: 'Remind unconfirmed petition signatures',
)]
class RemindUnconfirmedPetitionSignatureCommand extends Command
{
    public function __construct(
        private readonly PetitionSignatureRepository $repository,
        private readonly SignatureManager $manager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->repository->findAllToRemind() as $signature) {
            $this->manager->remind($signature);
        }

        return self::SUCCESS;
    }
}
