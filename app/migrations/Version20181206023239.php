<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181206023239 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE managed_areas (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED NOT NULL, tag_id INT UNSIGNED DEFAULT NULL, district_id INT UNSIGNED DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_7561DB9225F06C53 (adherent_id), INDEX IDX_7561DB92BAD26311 (tag_id), INDEX IDX_7561DB92B08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE managed_areas ADD CONSTRAINT FK_7561DB9225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE managed_areas ADD CONSTRAINT FK_7561DB92BAD26311 FOREIGN KEY (tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE managed_areas ADD CONSTRAINT FK_7561DB92B08FA272 FOREIGN KEY (district_id) REFERENCES districts (id)');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('DROP INDEX UNIQ_562C7DA3DC184E71 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP managed_area_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE managed_areas');
        $this->addSql('ALTER TABLE adherents ADD managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');
    }
}
