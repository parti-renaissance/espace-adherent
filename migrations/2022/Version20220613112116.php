<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220613112116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE deputy_managed_users_message');
        $this->addSql('DROP TABLE referent_managed_users_message');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE deputy_managed_users_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          district_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          offset BIGINT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_5AC419DD25F06C53 (adherent_id),
          INDEX IDX_5AC419DDB08FA272 (district_id),
          UNIQUE INDEX UNIQ_5AC419DDD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_managed_users_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          include_adherents_no_committee TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_adherents_in_committee TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_hosts TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_supervisors TINYINT(1) DEFAULT \'0\' NOT NULL,
          query_area_code LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          query_id LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          offset BIGINT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          interests LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_minimum INT DEFAULT NULL,
          age_maximum INT DEFAULT NULL,
          registered_from DATE DEFAULT NULL,
          registered_to DATE DEFAULT NULL,
          query_zone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_1E41AC6125F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_1E41AC61D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          deputy_managed_users_message
        ADD
          CONSTRAINT FK_5AC419DD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          deputy_managed_users_message
        ADD
          CONSTRAINT FK_5AC419DDB08FA272 FOREIGN KEY (district_id) REFERENCES districts (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_managed_users_message
        ADD
          CONSTRAINT FK_1E41AC6125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
