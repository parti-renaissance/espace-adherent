<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231207093329 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE administrator_role_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          administrator_id INT NOT NULL,
          author_id INT DEFAULT NULL,
          role VARCHAR(255) NOT NULL,
          action VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_CC6926CC4B09E92C (administrator_id),
          INDEX IDX_CC6926CCF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          administrator_role_history
        ADD
          CONSTRAINT FK_CC6926CC4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          administrator_role_history
        ADD
          CONSTRAINT FK_CC6926CCF675F31B FOREIGN KEY (author_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrator_role_history DROP FOREIGN KEY FK_CC6926CC4B09E92C');
        $this->addSql('ALTER TABLE administrator_role_history DROP FOREIGN KEY FK_CC6926CCF675F31B');
        $this->addSql('DROP TABLE administrator_role_history');
    }
}
