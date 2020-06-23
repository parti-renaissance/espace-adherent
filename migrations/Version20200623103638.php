<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200623103638 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE senatorial_candidate_areas (
          id INT AUTO_INCREMENT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE senatorial_candidate_areas_tags (
          senatorial_candidate_area_id INT NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_F83208FAA7BF84E8 (senatorial_candidate_area_id), 
          INDEX IDX_F83208FA9C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            senatorial_candidate_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          senatorial_candidate_areas_tags 
        ADD 
          CONSTRAINT FK_F83208FAA7BF84E8 FOREIGN KEY (senatorial_candidate_area_id) REFERENCES senatorial_candidate_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          senatorial_candidate_areas_tags 
        ADD 
          CONSTRAINT FK_F83208FA9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD senatorial_candidate_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3FCCAF6D5 FOREIGN KEY (
            senatorial_candidate_managed_area_id
          ) REFERENCES senatorial_candidate_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3FCCAF6D5 ON adherents (senatorial_candidate_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3FCCAF6D5');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP FOREIGN KEY FK_F83208FAA7BF84E8');
        $this->addSql('DROP TABLE senatorial_candidate_areas');
        $this->addSql('DROP TABLE senatorial_candidate_areas_tags');
        $this->addSql('DROP INDEX UNIQ_562C7DA3FCCAF6D5 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP senatorial_candidate_managed_area_id');
    }
}
