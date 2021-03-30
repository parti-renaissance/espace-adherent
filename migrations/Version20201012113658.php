<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201012113658 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_borough (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          postal_code LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          population INT DEFAULT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_1449587477153098 (code),
          INDEX IDX_144958748BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_borough
        ADD
          CONSTRAINT FK_144958748BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE geo_borough');
    }
}
