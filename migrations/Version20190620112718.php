<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190620112718 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        ADD 
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_D1D6095625F06C53 ON application_request_running_mate (adherent_id)');
        $this->addSql('ALTER TABLE application_request_volunteer ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          application_request_volunteer 
        ADD 
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_1139657025F06C53 ON application_request_volunteer (adherent_id)');

        $this->addSql(
            'UPDATE application_request_running_mate AS app
            INNER JOIN adherents AS a ON a.email_address = app.email_address
            SET app.adherent_id = a.id'
        );

        $this->addSql(
            'UPDATE application_request_volunteer AS app
            INNER JOIN adherents AS a ON a.email_address = app.email_address
            SET app.adherent_id = a.id'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate DROP FOREIGN KEY FK_D1D6095625F06C53');
        $this->addSql('DROP INDEX IDX_D1D6095625F06C53 ON application_request_running_mate');
        $this->addSql('ALTER TABLE application_request_running_mate DROP adherent_id');
        $this->addSql('ALTER TABLE application_request_volunteer DROP FOREIGN KEY FK_1139657025F06C53');
        $this->addSql('DROP INDEX IDX_1139657025F06C53 ON application_request_volunteer');
        $this->addSql('ALTER TABLE application_request_volunteer DROP adherent_id');
    }
}
