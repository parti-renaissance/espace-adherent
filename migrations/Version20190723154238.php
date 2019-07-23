<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190723154238 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE ideas_workshop_idea_notification_dates_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ideas_workshop_idea_notification_dates (id INT NOT NULL, last_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, caution_last_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE ideas_workshop_idea_notification_dates_id_seq CASCADE');
        $this->addSql('DROP TABLE ideas_workshop_idea_notification_dates');
    }
}
