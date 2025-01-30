<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230907113553 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_declared_mandate_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          added_mandates LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          removed_mandates LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          notified TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_A92880F925F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        ADD
          CONSTRAINT FK_A92880F925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_declared_mandate_history DROP FOREIGN KEY FK_A92880F925F06C53');
        $this->addSql('DROP TABLE adherent_declared_mandate_history');
    }
}
