<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SkillsDataTransferCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    protected function configure()
    {
        $this
            ->setName('app:skills:data_transfer')
            ->setDescription('Transfer the skill data to new skill table.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqlFromCommitteeMembership = <<<'SQL'
          ALTER TABLE skills ADD slug VARCHAR(255) NOT NULL;
          
          SET @id = SELECT LAST_INSERT_ID();
          
          DELIMITER //

          CREATE FUNCTION prc_test (var INT)
          BEGIN
              DECLARE  var2 INT;
              SET var2 = 1;
              SELECT  var2;
          END;
          //
        
          DELIMITER ;
          
          SET @id = NULL;
          
          DROP FUNCTION ;
          
          ALTER TABLE skills DROP slug;
          
          
          
          
          
          
          INSERT INTO projection_referent_managed_users
            (status, type, original_id, email, postal_code, city, country, first_name, last_name, age, phone,
            committees, is_committee_member, is_committee_host, is_mail_subscriber, created_at)
            SELECT
            0,
            'adherent',
            a.id,
            a.email_address,
            a.address_postal_code,
            a.address_city_name,
            a.address_country,
            a.first_name,
            a.last_name,
            TIMESTAMPDIFF(YEAR, a.birthdate, CURDATE()) AS age,
            a.phone,
            (
               SELECT GROUP_CONCAT(c.name SEPARATOR '|')
               FROM committees_memberships cm
               LEFT JOIN committees c ON cm.committee_uuid = c.uuid
               WHERE cm.adherent_id = a.id
            ),
            (
               SELECT COUNT(cm.id) > 0
               FROM committees_memberships cm
               LEFT JOIN committees c ON cm.committee_uuid = c.uuid
               WHERE cm.adherent_id = a.id AND c.status = 'APPROVED'
            ),
            (
               SELECT COUNT(cm.id) > 0
               FROM committees_memberships cm
               LEFT JOIN committees c ON cm.committee_uuid = c.uuid
               WHERE cm.adherent_id = a.id AND c.status = 'APPROVED' AND (cm.privilege = 'SUPERVISOR' OR cm.privilege = 'HOST')
            ),
            a.referents_emails_subscription,
            a.registered_at
            FROM adherents a
SQL;

        try {
            $stmt = $this->manager->getConnection()->prepare($sqlFromCommitteeMembership);
            $stmt->execute();

            $output->writeln('Data transfer of skills was successfully completed');
        } catch (\Exception $e) {
            $output->writeln('The error occurred during execution : '.$e->getMessage());
        }
    }
}
