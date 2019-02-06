<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190204134057 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD media_id BIGINT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD facebook_page_url VARCHAR(255) DEFAULT NULL, ADD twitter_page_url VARCHAR(255) DEFAULT NULL, ADD display_media TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('CREATE INDEX IDX_562C7DA3EA9FDD75 ON adherents (media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3EA9FDD75');
        $this->addSql('DROP INDEX IDX_562C7DA3EA9FDD75 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP media_id, DROP description, DROP facebook_page_url, DROP twitter_page_url, DROP display_media');
    }
}
