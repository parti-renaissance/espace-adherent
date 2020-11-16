<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200825022400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_mandate (id INT UNSIGNED AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', adherent_id INT UNSIGNED NOT NULL, committee_id INT UNSIGNED DEFAULT NULL, territorial_council_id INT UNSIGNED DEFAULT NULL, gender VARCHAR(6) NOT NULL, begin_at DATETIME NOT NULL, finish_at DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, quality VARCHAR(255) DEFAULT NULL, INDEX IDX_9C0C3D6025F06C53 (adherent_id), INDEX IDX_9C0C3D60ED1A100B (committee_id), INDEX IDX_9C0C3D60AAA61A99 (territorial_council_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherent_mandate ADD CONSTRAINT FK_9C0C3D6025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_mandate ADD CONSTRAINT FK_9C0C3D60ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_mandate ADD CONSTRAINT FK_9C0C3D60AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_mandate');
    }
}
