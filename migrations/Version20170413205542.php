<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170413205542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clarifications ADD keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE proposals ADD keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE articles ADD keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pages ADD media_id BIGINT DEFAULT NULL, ADD keywords VARCHAR(255) DEFAULT NULL, ADD display_media TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_2074E575EA9FDD75 ON pages (media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP keywords');
        $this->addSql('ALTER TABLE clarifications DROP keywords');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575EA9FDD75');
        $this->addSql('DROP INDEX IDX_2074E575EA9FDD75 ON pages');
        $this->addSql('ALTER TABLE pages DROP media_id, DROP keywords, DROP display_media');
        $this->addSql('ALTER TABLE proposals DROP keywords');
    }
}
