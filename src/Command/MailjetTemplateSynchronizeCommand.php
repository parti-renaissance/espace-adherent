<?php

namespace AppBundle\Command;

use AppBundle\Mailer\Message\MessageRegistry;
use AppBundle\Mailjet\EmailTemplateClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailjetTemplateSynchronizeCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:mailjet:synchronize-templates';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $messageRegistry;
    private $templateClientTransactional;
    private $templateClientCampaign;

    public function __construct(MessageRegistry $messageRegistry, EmailTemplateClient $templateClientTransactional, EmailTemplateClient $templateClientCampaign)
    {
        $this->messageRegistry = $messageRegistry;
        $this->templateClientTransactional = $templateClientTransactional;
        $this->templateClientCampaign = $templateClientCampaign;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Synchronizes email templates with Mailjet templates.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->section('Synchronizing transactional templates');
        $this->synchronizeTemplate($this->messageRegistry->getTransactionalMessages(), $this->templateClientTransactional);

        $this->io->section('Synchronizing campaign templates');
        $this->synchronizeTemplate($this->messageRegistry->getCampaignMessages(), $this->templateClientCampaign);

        $this->io->success('Templates synchronised');
    }

    private function synchronizeTemplate($templates, EmailTemplateClient $client): void
    {
        $this->io->progressStart(count($templates));

        foreach ($templates as $template) {
            if ($this->io->isVeryVerbose()) {
                $this->io->text("Synchronizing template $template");
            }

            $client->synchronize($template);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }
}
