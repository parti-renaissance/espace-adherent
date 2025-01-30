<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240925134021 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE administrator_action_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          administrator_id INT NOT NULL,
          TYPE VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          data JSON DEFAULT NULL,
          INDEX IDX_5A263AE84B09E92C (administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          administrator_action_history
        ADD
          CONSTRAINT FK_5A263AE84B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrator_action_history DROP FOREIGN KEY FK_5A263AE84B09E92C');
        $this->addSql('DROP TABLE administrator_action_history');
    }
}
