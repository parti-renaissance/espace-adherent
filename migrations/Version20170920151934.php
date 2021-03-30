<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170920151934 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE coordinator_managed_areas (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_C20973D25F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coordinator_managed_areas ADD CONSTRAINT FK_C20973D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE coordinator_managed_areas');
    }
}
