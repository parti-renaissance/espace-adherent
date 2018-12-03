<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181203124145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE communication_manager_areas (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE communication_manager_areas_tags (communication_manager_area_id INT NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_5EE7AD59755146FE (communication_manager_area_id), INDEX IDX_5EE7AD599C262DB3 (referent_tag_id), PRIMARY KEY(communication_manager_area_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE communication_manager_areas_tags ADD CONSTRAINT FK_5EE7AD59755146FE FOREIGN KEY (communication_manager_area_id) REFERENCES communication_manager_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE communication_manager_areas_tags ADD CONSTRAINT FK_5EE7AD599C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD communication_manager_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3755146FE FOREIGN KEY (communication_manager_area_id) REFERENCES communication_manager_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3755146FE ON adherents (communication_manager_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3755146FE');
        $this->addSql('ALTER TABLE communication_manager_areas_tags DROP FOREIGN KEY FK_5EE7AD59755146FE');
        $this->addSql('DROP TABLE communication_manager_areas');
        $this->addSql('DROP TABLE communication_manager_areas_tags');
        $this->addSql('DROP INDEX UNIQ_562C7DA3755146FE ON adherents');
        $this->addSql('ALTER TABLE adherents DROP communication_manager_area_id');
    }
}
