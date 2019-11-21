<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191121124258 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE senator_area (
          id INT AUTO_INCREMENT NOT NULL, 
          department_tag_id INT UNSIGNED DEFAULT NULL, 
          entire_world TINYINT(1) DEFAULT \'0\' NOT NULL, 
          UNIQUE INDEX UNIQ_D229BBF7AEC89CE1 (department_tag_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          senator_area 
        ADD 
          CONSTRAINT FK_D229BBF7AEC89CE1 FOREIGN KEY (department_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD senator_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA393494FA8 FOREIGN KEY (senator_area_id) REFERENCES senator_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA393494FA8 ON adherents (senator_area_id)');
        $this->addSql('ALTER TABLE referent_tags ADD type VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE referent_tags SET type = \'department\' WHERE code REGEXP \'^([0-9]{2,3}|2(A|B))$\'');
        $this->addSql('UPDATE referent_tags SET type = \'district\' WHERE type IS NULL AND code LIKE \'CIRCO_%\'');
        $this->addSql('UPDATE referent_tags SET type = \'country\' WHERE type IS NULL AND code REGEXP \'^[A-Z]{2}$\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA393494FA8');
        $this->addSql('DROP TABLE senator_area');
        $this->addSql('DROP INDEX UNIQ_562C7DA393494FA8 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP senator_area_id');
        $this->addSql('ALTER TABLE referent_tags DROP type');
    }
}
