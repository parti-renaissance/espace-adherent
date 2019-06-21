<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190621095409 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_volunteer ADD gender VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE application_request_running_mate ADD gender VARCHAR(6) DEFAULT NULL');

        $this->addSql(
            'UPDATE application_request_running_mate AS app
            INNER JOIN adherents AS a ON a.id = app.adherent_id
            SET app.gender = a.gender'
        );

        $this->addSql(
            'UPDATE application_request_volunteer AS app
            INNER JOIN adherents AS a ON a.id = app.adherent_id
            SET app.gender = a.gender'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate DROP gender');
        $this->addSql('ALTER TABLE application_request_volunteer DROP gender');
    }
}
