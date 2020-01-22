<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200122102717 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE consular_managed_area (
          id INT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consular_managed_area_referent_tag (
          consular_managed_area_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_5B83BDB0122E5FF4 (consular_managed_area_id),
          INDEX IDX_5B83BDB09C262DB3 (referent_tag_id),
          PRIMARY KEY(
            consular_managed_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consular_districts (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          countries LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
          code VARCHAR(6) NOT NULL,
          number SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(50) NOT NULL,
          UNIQUE INDEX UNIQ_F81AD13280E32C3E (geo_data_id),
          UNIQUE INDEX consular_district_code_unique (code),
          UNIQUE INDEX consular_district_referent_tag_unique (referent_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          consular_managed_area_referent_tag
        ADD
          CONSTRAINT FK_5B83BDB0122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          consular_managed_area_referent_tag
        ADD
          CONSTRAINT FK_5B83BDB09C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          consular_districts
        ADD
          CONSTRAINT FK_F81AD13280E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          consular_districts
        ADD
          CONSTRAINT FK_F81AD1329C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD consular_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3122E5FF4 ON adherents (consular_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3122E5FF4');
        $this->addSql('ALTER TABLE consular_managed_area_referent_tag DROP FOREIGN KEY FK_5B83BDB0122E5FF4');
        $this->addSql('DROP TABLE consular_managed_area');
        $this->addSql('DROP TABLE consular_managed_area_referent_tag');
        $this->addSql('DROP TABLE consular_districts');
        $this->addSql('DROP INDEX UNIQ_562C7DA3122E5FF4 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP consular_managed_area_id');
    }
}
