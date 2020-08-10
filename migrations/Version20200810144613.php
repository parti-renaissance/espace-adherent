<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200810144613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters CHANGE is_adherent is_adherent TINYINT(1) DEFAULT \'1\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters CHANGE is_adherent is_adherent TINYINT(1) DEFAULT NULL');
    }
}
