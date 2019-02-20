<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171028202706 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_managed_users_message (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, include_newsletter TINYINT(1) DEFAULT \'0\' NOT NULL, include_adherents_no_committee TINYINT(1) DEFAULT \'0\' NOT NULL, include_adherents_in_committee TINYINT(1) DEFAULT \'0\' NOT NULL, include_hosts TINYINT(1) DEFAULT \'0\' NOT NULL, include_supevisors TINYINT(1) DEFAULT \'0\' NOT NULL, query_area_code VARCHAR(255) NOT NULL, query_city VARCHAR(255) NOT NULL, query_id VARCHAR(255) NOT NULL, offset BIGINT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1E41AC6125F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referent_managed_users_message ADD CONSTRAINT FK_1E41AC6125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_managed_users_message');
    }
}
