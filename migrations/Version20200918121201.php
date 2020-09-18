<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200918121201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pages ADD header_media_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          pages 
        ADD 
          CONSTRAINT FK_2074E5755B42DC0F FOREIGN KEY (header_media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_2074E5755B42DC0F ON pages (header_media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E5755B42DC0F');
        $this->addSql('DROP INDEX IDX_2074E5755B42DC0F ON pages');
        $this->addSql('ALTER TABLE pages DROP header_media_id');
    }
}
