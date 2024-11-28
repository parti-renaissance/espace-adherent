<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241128143112 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_role_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          user_id INT UNSIGNED NOT NULL,
          admin_author_id INT DEFAULT NULL,
          user_author_id INT UNSIGNED DEFAULT NULL,
          role VARCHAR(255) NOT NULL,
          action VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          telegram_notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_1BBBA9B2A76ED395 (user_id),
          INDEX IDX_1BBBA9B21F82F629 (admin_author_id),
          INDEX IDX_1BBBA9B2F6957EFF (user_author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          user_role_history
        ADD
          CONSTRAINT FK_1BBBA9B2A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          user_role_history
        ADD
          CONSTRAINT FK_1BBBA9B21F82F629 FOREIGN KEY (admin_author_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          user_role_history
        ADD
          CONSTRAINT FK_1BBBA9B2F6957EFF FOREIGN KEY (user_author_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_role_history DROP FOREIGN KEY FK_1BBBA9B2A76ED395');
        $this->addSql('ALTER TABLE user_role_history DROP FOREIGN KEY FK_1BBBA9B21F82F629');
        $this->addSql('ALTER TABLE user_role_history DROP FOREIGN KEY FK_1BBBA9B2F6957EFF');
        $this->addSql('DROP TABLE user_role_history');
    }
}
