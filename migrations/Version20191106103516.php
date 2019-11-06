<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191106103516 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          projection_referent_managed_users 
        ADD 
          subscription_types LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
        DROP 
          is_mail_subscriber');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          projection_referent_managed_users 
        ADD 
          is_mail_subscriber TINYINT(1) NOT NULL, 
        DROP 
          subscription_types');
    }
}
