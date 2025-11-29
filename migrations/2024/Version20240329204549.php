<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240329204549 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA339054338');
        $this->addSql('DROP TABLE procuration_managed_areas');
        $this->addSql('DROP INDEX UNIQ_562C7DA339054338 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP procuration_managed_area_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE adherents ADD procuration_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA339054338 FOREIGN KEY (procuration_managed_area_id) REFERENCES procuration_managed_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA339054338 ON adherents (procuration_managed_area_id)');
    }
}
