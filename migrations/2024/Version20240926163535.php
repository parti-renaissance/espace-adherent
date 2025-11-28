<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926163535 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tax_receipt (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          donator_id INT UNSIGNED NOT NULL,
          label VARCHAR(255) NOT NULL,
          file_name VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_12D1164FD17F50A6 (uuid),
          INDEX IDX_12D1164F831BACAF (donator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          tax_receipt
        ADD
          CONSTRAINT FK_12D1164F831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tax_receipt DROP FOREIGN KEY FK_12D1164F831BACAF');
        $this->addSql('DROP TABLE tax_receipt');
    }
}
