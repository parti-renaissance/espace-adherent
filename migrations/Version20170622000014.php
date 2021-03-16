<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170622000014 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailjet_emails ADD delivered_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE mailjet_emails SET delivered_at = updated_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailjet_emails DROP delivered_at');
    }
}
