<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateManagedUsersByReferentCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;
    
    protected function configure()
    {
        $this
            ->setName('app:referent:populate')
            ->setDescription('Create managed users by referent from the datas of concerned tables.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqlFromCommitteeMembership =
            'INSERT INTO projection_referent_managed_users ' .
            '(status, type, original_id, email, postal_code, city, country, first_name, last_name, age, phone, ' .
            'committees, is_committee_member, is_committee_host, is_mail_subscriber, created_at) ' .
            'SELECT ' .
            '0,' .
            '\'adherent\',' .
            'a.id,' .
            'a.email_address,' .
            'a.address_postal_code,' .
            'a.address_city_name,' .
            'a.address_country,' .
            'a.first_name,' .
            'a.last_name,' .
            'TIMESTAMPDIFF(YEAR, a.birthdate, CURDATE()) AS age,' .
            'a.phone,' .
            '(' .
                'SELECT GROUP_CONCAT(c.name SEPARATOR \'|\') ' .
                'FROM committees_memberships cm ' .
                'LEFT JOIN committees c ON cm.committee_uuid = c.uuid ' .
                'WHERE cm.adherent_id = a.id' .
            '),' .
            '(' .
                'SELECT COUNT(cm.id) > 0 ' .
                'FROM committees_memberships cm ' .
                'LEFT JOIN committees c ON cm.committee_uuid = c.uuid ' .
                'WHERE cm.adherent_id = a.id AND c.status = \'APPROVED\'' .
            '),' .
            '(' .
                'SELECT COUNT(cm.id) > 0 ' .
                'FROM committees_memberships cm ' .
                'LEFT JOIN committees c ON cm.committee_uuid = c.uuid ' .
                'WHERE cm.adherent_id = a.id AND c.status = \'APPROVED\' AND (cm.privilege = \'SUPERVISOR\' OR cm.privilege = \'HOST\')' .
            '),' .
            'a.referents_emails_subscription, ' .
            'a.registered_at ' .
            'FROM adherents a';

        $sqlFromNewsletterSubscription =
            'INSERT INTO projection_referent_managed_users ' .
            '(status, type, original_id, email, postal_code, city, country, first_name, last_name, age, phone, ' .
            'committees, is_committee_member, is_committee_host, is_mail_subscriber, created_at) ' .
            'SELECT 0, \'newsletter\', n.id, n.email, n.postal_code, NULL, NULL, NULL, NULL, NULL, NULL, \'\', 0, 0, 1, n.created_at ' .
            'FROM newsletter_subscriptions n ' .
            'WHERE LENGTH(n.postal_code) = 5';

        $sqlStatus = 'UPDATE projection_referent_managed_users SET status = status . 1';

        try {
            $stmt = $this->manager->getConnection()->prepare($sqlFromCommitteeMembership);
            $stmt->execute();
            $stmt = $this->manager->getConnection()->prepare($sqlFromNewsletterSubscription);
            $stmt->execute();
            $stmt = $this->manager->getConnection()->prepare($sqlStatus);
            $stmt->execute();
        } catch (\Exception $e) {
            $output->writeln('The error occurred during execution : ' . $e->getMessage());
        }

        $output->writeln('Creation of managed users by referent was successfully completed');
    }
}
