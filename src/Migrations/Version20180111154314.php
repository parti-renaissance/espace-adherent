<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180111154314 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD84888FDEEE');
        $this->addSql('ALTER TABLE procuration_requests ADD CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (procuration_request_found_by_id) REFERENCES adherents (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD84888FDEEE');
        $this->addSql('ALTER TABLE procuration_requests ADD CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (procuration_request_found_by_id) REFERENCES adherents (id)');
    }
}
