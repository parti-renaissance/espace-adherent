<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190419111715 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD citizen_project_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94B3584533 ON adherent_message_filters (citizen_project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94B3584533');
        $this->addSql('DROP INDEX IDX_28CA9F94B3584533 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP citizen_project_id');
    }
}
