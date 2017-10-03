<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171005145433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE referent_areas (referent_id SMALLINT UNSIGNED NOT NULL, area_id SMALLINT UNSIGNED NOT NULL, INDEX IDX_75CEBC6C35E47E35 (referent_id), INDEX IDX_75CEBC6CBD0F409C (area_id), PRIMARY KEY(referent_id, area_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id)');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE referent_areas');
    }
}
