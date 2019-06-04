<?php

namespace AppBundle\Command;

use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Mailchimp\Webhook\WebhookHandler;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpUpdateAdherentCommand extends Command
{
    public static $defaultName = 'mailchimp:update:adherents';

    /** @var SymfonyStyle */
    private $io;
    private $manager;
    private $webhookHandler;
    private $mailchimpObjectIdMapping;

    public function __construct(
        ObjectManager $manager,
        WebhookHandler $webhookHandler,
        MailchimpObjectIdMapping $mailchimpObjectIdMapping
    ) {
        $this->manager = $manager;
        $this->webhookHandler = $webhookHandler;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addOption('unsubscribe', null, InputOption::VALUE_NONE, 'Update only unsubscribed adherents')
            ->addOption('email-col-index', null, InputOption::VALUE_OPTIONAL, 'Index of email column in CSV', 0)
            ->addOption('email-pref-col-index', null, InputOption::VALUE_OPTIONAL, 'Index of email preferences column in CSV', 10)
            ->addOption('interest-col-index', null, InputOption::VALUE_OPTIONAL, 'Index of interest column in CSV', 11)
            ->setDescription('Update adherents from Mailchimp exported data (CSV)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('file');

        if (false === ($file = fopen($filePath, 'rb'))) {
            throw new \InvalidArgumentException(sprintf('File %s is not readable', $filePath));
        }

        $header = fgetcsv($file);

        $emailColIndex = (int) $input->getOption('email-col-index');
        $emailPrefColIndex = (int) $input->getOption('email-pref-col-index');
        $interestColIndex = (int) $input->getOption('interest-col-index');

        $unsubscribeProcess = $input->getOption('unsubscribe');

        $this->validateHeaders($header, $emailColIndex, $emailPrefColIndex, $interestColIndex);

        $count = 0;

        while ($row = fgetcsv($file)) {
            ++$count;

            if ($unsubscribeProcess) {
                $this->webhookHandler->handle(EventTypeEnum::UNSUBSCRIBE, ['email' => $row[$emailColIndex]]);
            } else {
                $this->webhookHandler->handle(
                    EventTypeEnum::UPDATE_PROFILE,
                    [
                        'email' => $row[$emailColIndex],
                        'merges' => [
                            'GROUPINGS' => [
                                [
                                    'unique_id' => $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId(),
                                    'groups' => $row[$emailPrefColIndex],
                                ],
                                [
                                    'unique_id' => $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId(),
                                    'groups' => $row[$interestColIndex],
                                ],
                            ],
                        ],
                    ]
                );
            }

            if (0 === $count % 1000) {
                $this->manager->clear();
            }
        }
    }

    private function validateHeaders(
        array $header,
        int $emailColIndex,
        int $emailPrefColIndex,
        int $interestColIndex
    ): void {
        if ('Adresse mail' !== $header[$emailColIndex]) {
            throw new \InvalidArgumentException(sprintf('It seems that position of email column is not %d or its label is not equal to `Adresse mail`', $emailColIndex));
        }

        if ('Mes notifications via e-mail' !== $header[$emailPrefColIndex]) {
            throw new \InvalidArgumentException(sprintf('It seems that position of email preferences column is not %d or its label is not equal to `Mes notifications via e-mail`', $emailPrefColIndex));
        }

        if ('Mes centres d\'intérêt' !== $header[$interestColIndex]) {
            throw new \InvalidArgumentException(sprintf('It seems that position of interests column is not %d or its label is not equal to `Mes centres d\'intérêt`', $interestColIndex));
        }
    }
}
