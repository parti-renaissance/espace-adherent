<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427153956 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC3BF0CCB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC5C34CBC4');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74583B12DAC');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745ED1A100B');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA74583B12DAC FOREIGN KEY (community_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC3BF0CCB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC5C34CBC4');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745ED1A100B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74583B12DAC');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA74583B12DAC FOREIGN KEY (community_event_id) REFERENCES events (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
