<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220816153624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE crm_downloads (
          `index` BIGINT AUTO_INCREMENT NOT NULL,
          date DATE NOT NULL,
          zone_type VARCHAR(255) NOT NULL,
          zone_name VARCHAR(255) NOT NULL,
          unique_user BIGINT NOT NULL,
          download_per1000 DOUBLE PRECISION NOT NULL,
          INDEX IDX_A19599880736701 (`index`),
          PRIMARY KEY(`index`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crmusage (
          `index` BIGINT AUTO_INCREMENT NOT NULL,
          date DATE NOT NULL,
          zone_type VARCHAR(255) NOT NULL,
          zone_name VARCHAR(255) NOT NULL,
          unique_user BIGINT NOT NULL,
          INDEX IDX_E2120CDA80736701 (`index`),
          PRIMARY KEY(`index`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE crm_downloads');
        $this->addSql('DROP TABLE crmusage');
    }
}
