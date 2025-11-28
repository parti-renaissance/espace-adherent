<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231018081458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE administrator_role (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          label VARCHAR(255) NOT NULL,
          enabled TINYINT(1) DEFAULT 0 NOT NULL,
          group_code VARCHAR(255) NOT NULL,
          description VARCHAR(255) NOT NULL,
          UNIQUE INDEX UNIQ_DEE3E68777153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE administrators_roles (
          administrator_id INT NOT NULL,
          administrator_role_id INT UNSIGNED NOT NULL,
          INDEX IDX_9BCFB8EB4B09E92C (administrator_id),
          INDEX IDX_9BCFB8EBB31C2F43 (administrator_role_id),
          PRIMARY KEY(
            administrator_id, administrator_role_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          administrators_roles
        ADD
          CONSTRAINT FK_9BCFB8EB4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          administrators_roles
        ADD
          CONSTRAINT FK_9BCFB8EBB31C2F43 FOREIGN KEY (administrator_role_id) REFERENCES administrator_role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators_roles DROP FOREIGN KEY FK_9BCFB8EB4B09E92C');
        $this->addSql('ALTER TABLE administrators_roles DROP FOREIGN KEY FK_9BCFB8EBB31C2F43');
        $this->addSql('DROP TABLE administrator_role');
        $this->addSql('DROP TABLE administrators_roles');
    }
}
