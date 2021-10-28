<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210402115202 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE coalition_moderator_role_association (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherents ADD coalition_moderator_role_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA38828ED30 FOREIGN KEY (coalition_moderator_role_id) REFERENCES coalition_moderator_role_association (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA38828ED30 ON adherents (coalition_moderator_role_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA38828ED30');
        $this->addSql('DROP TABLE coalition_moderator_role_association');
        $this->addSql('DROP INDEX UNIQ_562C7DA38828ED30 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP coalition_moderator_role_id');
    }
}
