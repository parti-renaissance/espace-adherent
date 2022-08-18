<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220816180602 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jemengage_mobile_app_download (
          id BIGINT AUTO_INCREMENT NOT NULL,
          date DATE NOT NULL,
          zone_type VARCHAR(255) NOT NULL,
          zone_name VARCHAR(255) NOT NULL,
          unique_user BIGINT NOT NULL,
          cum_sum INT NOT NULL,
          downloads_per1000 DOUBLE PRECISION NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jemengage_mobile_app_usage (
          id BIGINT AUTO_INCREMENT NOT NULL,
          date DATE NOT NULL,
          zone_type VARCHAR(255) NOT NULL,
          zone_name VARCHAR(255) NOT NULL,
          unique_user BIGINT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jemengage_mobile_app_download');
        $this->addSql('DROP TABLE jemengage_mobile_app_usage');
    }
}
