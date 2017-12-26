<?php

namespace AppBundle\Command;

use AppBundle\Entity\Administrator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailerMigrateRolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:mailer:migrate-roles')
            ->setDescription('Updates administrators role "ROLE_ADMIN_MAILJET" to "ROLE_ADMIN_EMAIL"')
            ->addOption(
                'revert',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Specify this argument to revert',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (false === $input->getOption('revert')) {
            $search = 'ROLE_ADMIN_MAILJET';
            $replace = 'ROLE_ADMIN_EMAIL';
        } else {
            $search = 'ROLE_ADMIN_EMAIL';
            $replace = 'ROLE_ADMIN_MAILJET';
        }

        $output->writeln(sprintf('Updating administrators role "%s" to "%s"', $search, $replace));

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($em->getRepository(Administrator::class)->findAll() as $admin) {
            $roles = $admin->getRoles();

            if (!$key = array_search($search, $roles, true)) {
                continue;
            }

            $roles[$key] = $replace;

            $admin->setRoles($roles);

            $output->writeln(sprintf('Changed role for "%s"', $admin->getEmailAddress()));
        }

        $em->flush();
    }
}
