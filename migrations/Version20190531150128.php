<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190531150128 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          email_unsubscribed TINYINT(1) DEFAULT \'0\' NOT NULL, 
        ADD 
          email_unsubscribed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP email_unsubscribed, DROP email_unsubscribed_at');
    }
}
