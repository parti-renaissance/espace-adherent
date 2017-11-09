<?php

namespace AppBundle\Command;

use AppBundle\Entity\Administrator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailerMigrateRolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:mailer:migrate-roles')
            ->setDescription('Updates administrators role "ROLE_ADMIN_MAILJET" to "ROLE_ADMIN_EMAIL"')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $output->writeln('Updating administrators role "ROLE_ADMIN_MAILJET" to "ROLE_ADMIN_EMAIL"');

        foreach ($em->getRepository(Administrator::class)->findAll() as $admin) {
            $roles = $admin->getRoles();

            if (!$key = array_search('ROLE_ADMIN_MAILJET', $roles, true)) {
                continue;
            }

            $roles[$key] = 'ROLE_ADMIN_EMAIL';

            $admin->setRoles($roles);

            $output->writeln(sprintf('Changed role for "%s"', $admin->getEmailAddress()));
        }

        $em->flush();
    }
}
