<?php

declare(strict_types=1);

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
          administrator_id INT DEFAULT NULL,
        ADD
          notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        ADD
          CONSTRAINT FK_A92880F94B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_A92880F94B09E92C ON adherent_declared_mandate_history (administrator_id)');

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
        $this->addSql('ALTER TABLE adherent_declared_mandate_history DROP FOREIGN KEY FK_A92880F94B09E92C');
        $this->addSql('DROP INDEX IDX_A92880F94B09E92C ON adherent_declared_mandate_history');
        $this->addSql('ALTER TABLE
          adherent_declared_mandate_history
        DROP
          administrator_id,
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
