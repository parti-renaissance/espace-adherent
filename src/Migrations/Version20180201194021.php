<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180201194021 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(16) DEFAULT \'unresolved\' NOT NULL');
        $this->addSql('ALTER TABLE reports ADD citizen_action_id INT UNSIGNED DEFAULT NULL, ADD committee_id INT UNSIGNED DEFAULT NULL, ADD community_event_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745A2DD3412 FOREIGN KEY (citizen_action_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA74583B12DAC FOREIGN KEY (community_event_id) REFERENCES events (id)');
        $this->addSql('CREATE INDEX IDX_F11FA745A2DD3412 ON reports (citizen_action_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745ED1A100B ON reports (committee_id)');
        $this->addSql('CREATE INDEX IDX_F11FA74583B12DAC ON reports (community_event_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745A2DD3412');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745ED1A100B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74583B12DAC');
        $this->addSql('DROP INDEX IDX_F11FA745A2DD3412 ON reports');
        $this->addSql('DROP INDEX IDX_F11FA745ED1A100B ON reports');
        $this->addSql('DROP INDEX IDX_F11FA74583B12DAC ON reports');
        $this->addSql('ALTER TABLE reports DROP citizen_action_id, DROP committee_id, DROP community_event_id');
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci');
    }
}
