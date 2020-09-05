<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200905024222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE foreign_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          number SMALLINT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          UNIQUE INDEX UNIQ_7232D34577153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE geo_country ADD foreign_district_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          CONSTRAINT FK_E465446472D24D35 FOREIGN KEY (foreign_district_id) REFERENCES foreign_district (id)');
        $this->addSql('CREATE INDEX IDX_E465446472D24D35 ON geo_country (foreign_district_id)');
        $this->addSql('ALTER TABLE
          consular_district
        ADD
          foreign_district_id INT UNSIGNED DEFAULT NULL,
        ADD
          name VARCHAR(255) NOT NULL,
        DROP
          countries');
        $this->addSql('ALTER TABLE
          consular_district
        ADD
          CONSTRAINT FK_77152B8872D24D35 FOREIGN KEY (foreign_district_id) REFERENCES foreign_district (id)');
        $this->addSql('CREATE INDEX IDX_77152B8872D24D35 ON consular_district (foreign_district_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_country DROP FOREIGN KEY FK_E465446472D24D35');
        $this->addSql('ALTER TABLE consular_district DROP FOREIGN KEY FK_77152B8872D24D35');
        $this->addSql('DROP TABLE foreign_district');
        $this->addSql('DROP INDEX IDX_77152B8872D24D35 ON consular_district');
        $this->addSql('ALTER TABLE
          consular_district
        ADD
          countries LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\',
        DROP
          foreign_district_id,
        DROP
          name');
        $this->addSql('DROP INDEX IDX_E465446472D24D35 ON geo_country');
        $this->addSql('ALTER TABLE geo_country DROP foreign_district_id');
    }
}
