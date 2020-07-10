<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200710144759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lre_area (
          id INT AUTO_INCREMENT NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_8D3B8F189C262DB3 (referent_tag_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          lre_area 
        ADD 
          CONSTRAINT FK_8D3B8F189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD lre_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA379645AD5 FOREIGN KEY (lre_area_id) REFERENCES lre_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379645AD5 ON adherents (lre_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379645AD5');
        $this->addSql('DROP TABLE lre_area');
        $this->addSql('DROP INDEX UNIQ_562C7DA379645AD5 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP lre_area_id');
    }
}
