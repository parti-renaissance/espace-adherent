<?php

namespace Migrations;

use App\Instance\InstanceQualityEnum;
use App\Instance\InstanceQualityScopeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210616114059 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_instance_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          instance_quality_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          date DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_D63B17FA25F06C53 (adherent_id),
          INDEX IDX_D63B17FAA623BBD7 (instance_quality_id),
          INDEX IDX_D63B17FA9F2C3FAB (zone_id),
          INDEX IDX_D63B17FAAAA61A99 (territorial_council_id),
          UNIQUE INDEX adherent_instance_quality_unique (
            adherent_id, instance_quality_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instance_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          scopes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_BB26C6D377153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql(
            'INSERT INTO instance_quality(id, `code`, scopes, uuid, created_at, updated_at) VALUES '.implode(', ', array_map(function (string $code) {
                return sprintf('(null, "%s", "%s", UUID(), NOW(), NOW())', $code, InstanceQualityScopeEnum::NATIONAL_COUNCIL);
            }, InstanceQualityEnum::toArray()))
        );

        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAA623BBD7 FOREIGN KEY (instance_quality_id) REFERENCES instance_quality (id)');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FAA623BBD7');
        $this->addSql('DROP TABLE adherent_instance_quality');
        $this->addSql('DROP TABLE instance_quality');
    }
}
