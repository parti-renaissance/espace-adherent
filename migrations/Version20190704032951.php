<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190704032951 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE administrator_export_history (id INT UNSIGNED AUTO_INCREMENT NOT NULL, administrator_id INT NOT NULL, route_name VARCHAR(255) NOT NULL, parameters JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_10499F014B09E92C (administrator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE administrator_export_history ADD CONSTRAINT FK_10499F014B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE administrator_export_history');
    }
}
