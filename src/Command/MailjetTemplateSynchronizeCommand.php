<?php

namespace AppBundle\Command;

use AppBundle\Entity\MailjetTemplate;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailjetTemplateSynchronizeCommand extends ContainerAwareCommand
{
    /**
     * @var \AppBundle\Mailjet\EmailTemplateClient
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    protected function configure()
    {
        $this
            ->setName('app:mailjet:synchronize-templates')
            ->setDescription('Synchronizes email templates with Mailjet templates.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->client = $this->getContainer()->get('app.mailer.template_client.transactional');
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = $this->manager->getRepository(MailjetTemplate::class)->findAll();

        foreach ($templates as $template) {
            $output->writeln(sprintf('Synchronizing template "%s"', $template->getName()));

            $this->client->synchronize($template);

            $this->manager->persist($template);
        }

        $this->manager->flush();
    }
}
