<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240221091851 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          status VARCHAR(255) DEFAULT \'pending\' NOT NULL');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          CONSTRAINT FK_C332555725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_C332555725F06C53 ON national_event_inscription (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C332555725F06C53');
        $this->addSql('DROP INDEX IDX_C332555725F06C53 ON national_event_inscription');
        $this->addSql('ALTER TABLE national_event_inscription DROP adherent_id, DROP status');
    }
}
