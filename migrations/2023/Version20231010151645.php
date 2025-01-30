<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231010151645 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_notification (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          type VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_F867267A25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_notification
        ADD
          CONSTRAINT FK_F867267A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_notification DROP FOREIGN KEY FK_F867267A25F06C53');
        $this->addSql('DROP TABLE adherent_notification');
    }
}
