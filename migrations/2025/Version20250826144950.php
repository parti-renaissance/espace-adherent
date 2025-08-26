<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826144950 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A0958635E47E35');
        $this->addSql('DROP INDEX IDX_74A0958635E47E35 ON app_hit');
        $this->addSql('ALTER TABLE
          app_hit
        CHANGE
          referent_id referrer_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          referent_code referrer_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A09586798C22DB FOREIGN KEY (referrer_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_74A09586798C22DB ON app_hit (referrer_id)');
        $this->addSql('ALTER TABLE
          events_registrations
        ADD
          referrer_id INT UNSIGNED DEFAULT NULL,
        ADD
          referrer_code VARCHAR(255) DEFAULT NULL,
        ADD
          utm_source VARCHAR(255) DEFAULT NULL,
        ADD
          utm_campaign VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          events_registrations
        ADD
          CONSTRAINT FK_EEFA30C0798C22DB FOREIGN KEY (referrer_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_EEFA30C0798C22DB ON events_registrations (referrer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A09586798C22DB');
        $this->addSql('DROP INDEX IDX_74A09586798C22DB ON app_hit');
        $this->addSql('ALTER TABLE
          app_hit
        CHANGE
          referrer_id referent_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          referrer_code referent_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A0958635E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON
        UPDATE
          NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_74A0958635E47E35 ON app_hit (referent_id)');
        $this->addSql('ALTER TABLE events_registrations DROP FOREIGN KEY FK_EEFA30C0798C22DB');
        $this->addSql('DROP INDEX IDX_EEFA30C0798C22DB ON events_registrations');
        $this->addSql('ALTER TABLE
          events_registrations
        DROP
          referrer_id,
        DROP
          referrer_code,
        DROP
          utm_source,
        DROP
          utm_campaign');
    }
}
