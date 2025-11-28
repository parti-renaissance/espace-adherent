<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230623101144 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE campus_registration (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          event_maker_id VARCHAR(50) NOT NULL,
          campus_event_id VARCHAR(50) NOT NULL,
          event_maker_order_uid VARCHAR(50) NOT NULL,
          status VARCHAR(255) NOT NULL,
          registered_at DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_30249D7BD17F50A6 (uuid),
          INDEX IDX_30249D7B25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          campus_registration
        ADD
          CONSTRAINT FK_30249D7B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campus_registration DROP FOREIGN KEY FK_30249D7B25F06C53');
        $this->addSql('DROP TABLE campus_registration');
    }
}
