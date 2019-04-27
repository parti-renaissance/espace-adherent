<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170411110645 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_requests ADD procuration_request_found_by_id INT UNSIGNED DEFAULT NULL, ADD reminded INT NOT NULL');
        $this->addSql('ALTER TABLE procuration_requests ADD CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (procuration_request_found_by_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_9769FD84888FDEEE ON procuration_requests (procuration_request_found_by_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD84888FDEEE');
        $this->addSql('DROP INDEX IDX_9769FD84888FDEEE ON procuration_requests');
        $this->addSql('ALTER TABLE procuration_requests DROP procuration_request_found_by_id, DROP reminded');
    }
}
