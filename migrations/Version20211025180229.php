<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211025180229 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_member_history ADD team_manager_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          team_member_history
        ADD
          CONSTRAINT FK_1F33062846E746A6 FOREIGN KEY (team_manager_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX team_member_history_team_manager_id_idx ON team_member_history (team_manager_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_member_history DROP FOREIGN KEY FK_1F33062846E746A6');
        $this->addSql('DROP INDEX team_member_history_team_manager_id_idx ON team_member_history');
        $this->addSql('ALTER TABLE team_member_history DROP team_manager_id');
    }
}
