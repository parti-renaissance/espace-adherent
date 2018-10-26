<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181026111913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey DROP phone, DROP agreed_to_join_paris_operation');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey ADD phone VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD agreed_to_join_paris_operation TINYINT(1) NOT NULL');
    }
}
