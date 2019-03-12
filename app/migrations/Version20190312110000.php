<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190312110000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          interactive_invitations 
        DROP 
          friend_email_address, 
          CHANGE friend_position friend_position VARCHAR(50) DEFAULT NULL');
        $this->addSql('UPDATE administrators SET roles = REPLACE(roles,\'ROLE_ADMIN_PURCHASING_POWER\',\'ROLE_ADMIN_MY_EUROPE\')');
        $this->addSql('DELETE FROM interactive_choices WHERE step = 1');
        $this->addSql('UPDATE interactive_choices SET type = \'my_europe\'');
        $this->addSql('UPDATE interactive_choices SET step = 1 WHERE step = 2');
        $this->addSql('UPDATE interactive_choices SET step = 2 WHERE step = 3');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          interactive_invitations 
        ADD 
          friend_email_address VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          CHANGE friend_position friend_position VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('UPDATE administrators SET administrators.roles = REPLACE(administrators.roles,\'ROLE_ADMIN_MY_EUROPE\',\'ROLE_ADMIN_PURCHASING_POWER\')');
    }
}
