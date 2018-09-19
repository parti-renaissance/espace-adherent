<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180918171508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_data (id INT UNSIGNED AUTO_INCREMENT NOT NULL, geo_shape GEOMETRY NOT NULL COMMENT \'(DC2Type:geometry)\', SPATIAL INDEX geo_data_geo_shape_idx (geo_shape), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP INDEX district_geo_shape_idx ON districts');
        $this->addSql('ALTER TABLE districts ADD geo_data_id INT UNSIGNED NOT NULL, DROP geo_shape');
        $this->addSql('ALTER TABLE districts ADD CONSTRAINT FK_68E318DC80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68E318DC80E32C3E ON districts (geo_data_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC80E32C3E');
        $this->addSql('DROP TABLE geo_data');
        $this->addSql('DROP INDEX UNIQ_68E318DC80E32C3E ON districts');
        $this->addSql('ALTER TABLE districts ADD geo_shape GEOMETRY NOT NULL COMMENT \'(DC2Type:geometry)\', DROP geo_data_id');
        $this->addSql('CREATE SPATIAL INDEX district_geo_shape_idx ON districts (geo_shape)');
    }
}
