<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240605204832 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A876C4DDA');
        $this->addSql('DROP INDEX IDX_5387574A876C4DDA ON events');
        $this->addSql('ALTER TABLE
          events
        ADD
          author_role VARCHAR(255) DEFAULT NULL,
        ADD
          author_instance VARCHAR(255) DEFAULT NULL,
        ADD
          author_zone VARCHAR(255) DEFAULT NULL,
        CHANGE
          organizer_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_5387574AF675F31B ON events (author_id)');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          author_role VARCHAR(255) DEFAULT NULL,
        ADD
          author_instance VARCHAR(255) DEFAULT NULL,
        ADD
          author_zone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          vox_action
        ADD
          author_role VARCHAR(255) DEFAULT NULL,
        ADD
          author_instance VARCHAR(255) DEFAULT NULL,
        ADD
          author_zone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `events` DROP FOREIGN KEY FK_5387574AF675F31B');
        $this->addSql('DROP INDEX IDX_5387574AF675F31B ON `events`');
        $this->addSql('ALTER TABLE
          `events`
        DROP
          author_role,
        DROP
          author_instance,
        DROP
          author_zone,
        CHANGE
          author_id organizer_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          `events`
        ADD
          CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON UPDATE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5387574A876C4DDA ON `events` (organizer_id)');
        $this->addSql('ALTER TABLE jecoute_news DROP author_role, DROP author_instance, DROP author_zone');
        $this->addSql('ALTER TABLE vox_action DROP author_role, DROP author_instance, DROP author_zone');
    }
}
