<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230718161858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_formation_print_by_adherents (
          formation_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          INDEX IDX_881E4C655200282E (formation_id),
          INDEX IDX_881E4C6525F06C53 (adherent_id),
          PRIMARY KEY(formation_id, adherent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_formation_print_by_adherents
        ADD
          CONSTRAINT FK_881E4C655200282E FOREIGN KEY (formation_id) REFERENCES adherent_formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_formation_print_by_adherents
        ADD
          CONSTRAINT FK_881E4C6525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_formation_print_by_adherents DROP FOREIGN KEY FK_881E4C655200282E');
        $this->addSql('ALTER TABLE adherent_formation_print_by_adherents DROP FOREIGN KEY FK_881E4C6525F06C53');
        $this->addSql('DROP TABLE adherent_formation_print_by_adherents');
    }
}
