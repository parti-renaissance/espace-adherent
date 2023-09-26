<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230926123728 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        ADD
          notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        $this->addSql('UPDATE adherent_declared_mandate_history
        SET notified_at = `date`
        WHERE notified = TRUE');

        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        DROP
          notified');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        ADD
          notified TINYINT(1) DEFAULT 0 NOT NULL');

        $this->addSql('UPDATE adherent_declared_mandate_history
        SET notified = TRUE
        WHERE notified_at IS NOT NULL');

        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        DROP
          notified_at');
    }
}
