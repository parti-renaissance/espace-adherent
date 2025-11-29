<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240924075613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_action_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          impersonificator_id INT DEFAULT NULL,
          TYPE VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          data JSON DEFAULT NULL,
          INDEX IDX_969D27F325F06C53 (adherent_id),
          INDEX IDX_969D27F32BDDCA0B (impersonificator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          user_action_history
        ADD
          CONSTRAINT FK_969D27F325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          user_action_history
        ADD
          CONSTRAINT FK_969D27F32BDDCA0B FOREIGN KEY (impersonificator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_action_history DROP FOREIGN KEY FK_969D27F325F06C53');
        $this->addSql('ALTER TABLE user_action_history DROP FOREIGN KEY FK_969D27F32BDDCA0B');
        $this->addSql('DROP TABLE user_action_history');
    }
}
