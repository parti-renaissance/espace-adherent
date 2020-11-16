<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200811181456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_membership_log (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED NOT NULL, type VARCHAR(20) NOT NULL, description VARCHAR(255) NOT NULL, quality_name VARCHAR(50) NOT NULL, actual_territorial_council VARCHAR(255) DEFAULT NULL, actual_quality_names LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', found_territorial_councils LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', created_at DATETIME NOT NULL, is_resolved TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_2F6D242025F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_membership_log ADD CONSTRAINT FK_2F6D242025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_membership_log');
    }
}
