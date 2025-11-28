<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240527140711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0F675F31B');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE jecoute_riposte DROP FOREIGN KEY FK_17E1064BF675F31B');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F675F31B');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE vox_action DROP FOREIGN KEY FK_C721ED96F675F31B');
        $this->addSql('ALTER TABLE vox_action ADD status VARCHAR(255) DEFAULT \'scheduled\' NOT NULL');
        $this->addSql('ALTER TABLE
          vox_action
        ADD
          CONSTRAINT FK_C721ED96F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE vox_action_participant DROP FOREIGN KEY FK_9A5816DC25F06C53');
        $this->addSql('ALTER TABLE
          vox_action_participant
        ADD
          CONSTRAINT FK_9A5816DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0F675F31B');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE jecoute_riposte DROP FOREIGN KEY FK_17E1064BF675F31B');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F675F31B');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE vox_action DROP FOREIGN KEY FK_C721ED96F675F31B');
        $this->addSql('ALTER TABLE vox_action DROP status');
        $this->addSql('ALTER TABLE
          vox_action
        ADD
          CONSTRAINT FK_C721ED96F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE vox_action_participant DROP FOREIGN KEY FK_9A5816DC25F06C53');
        $this->addSql('ALTER TABLE
          vox_action_participant
        ADD
          CONSTRAINT FK_9A5816DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
