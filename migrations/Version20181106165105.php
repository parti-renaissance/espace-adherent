<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181106165105 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey 
            ADD agreed_to_treat_personal_data TINYINT(1) NOT NULL, 
            ADD profession VARCHAR(15) DEFAULT NULL, 
            CHANGE agreed_to_join agreed_to_contact_for_join TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey 
            DROP agreed_to_treat_personal_data,
            DROP profession,
            CHANGE agreed_to_contact_for_join agreed_to_join TINYINT(1) NOT NULL
        ');
    }
}
