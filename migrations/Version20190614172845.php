<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190614172845 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jecoute_managed_areas (
          id INT AUTO_INCREMENT NOT NULL, 
          codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherents ADD jecoute_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA394E3BB99 FOREIGN KEY (jecoute_managed_area_id) REFERENCES jecoute_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA394E3BB99 ON adherents (jecoute_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA394E3BB99');
        $this->addSql('DROP TABLE jecoute_managed_areas');
        $this->addSql('DROP INDEX UNIQ_562C7DA394E3BB99 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP jecoute_managed_area_id');
    }
}
