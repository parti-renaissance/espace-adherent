<?php

namespace App\Command;

use App\Mailchimp\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpDeleteStaticSegmentCommand extends Command
{
    protected static $defaultName = 'mailchimp:segment:delete-static';

    /** @var SymfonyStyle */
    private $io;
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete static segments')
            ->addArgument(
                'ids',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Static segment Mailchimp ids (`id1,id2,id3` or `id1 id2 id3`)'
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ids = $input->getArgument('ids');

        if (1 === \count($ids)) {
            $ids = explode(',', $ids[0]);
        }

        $this->io->progressStart(\count($ids));

        $success = $errors = [];

        foreach ($ids as $id) {
            if ($this->manager->deleteStaticSegment($id)) {
                $success[] = $id;
            } else {
                $errors[] = $id;
            }
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success(sprintf('%d segments are deleted', \count($success)));

        if ($errors) {
            $this->io->warning('These segments have not been deleted');
            $this->io->comment(implode(',', $errors));
        }
    }
}
