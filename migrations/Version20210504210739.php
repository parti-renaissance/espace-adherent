<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210504210739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD cause_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F9466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F9466E2221E ON adherent_message_filters (cause_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F9466E2221E');
        $this->addSql('DROP INDEX IDX_28CA9F9466E2221E ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP cause_id');
    }
}
