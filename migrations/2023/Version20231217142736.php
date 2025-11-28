<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231217142736 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_activation_code (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          value VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          INDEX IDX_3A628E8F25F06C53 (adherent_id),
          INDEX IDX_3A628E8F25F06C531D775834 (adherent_id, value),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_activation_code
        ADD
          CONSTRAINT FK_3A628E8F25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          finished_adhesion_steps LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_activation_code DROP FOREIGN KEY FK_3A628E8F25F06C53');
        $this->addSql('DROP TABLE adherent_activation_code');
        $this->addSql('ALTER TABLE adherents DROP finished_adhesion_steps');
    }
}
