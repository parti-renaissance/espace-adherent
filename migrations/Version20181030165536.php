<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181030165536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE jecoute_data_survey 
            ADD agreed_to_join TINYINT(1) NOT NULL, 
            ADD postal_code VARCHAR(5) DEFAULT NULL, 
            ADD age_range VARCHAR(15) DEFAULT NULL, 
            ADD gender VARCHAR(15) DEFAULT NULL, 
            ADD gender_other VARCHAR(50) DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey DROP agreed_to_join, DROP postal_code, DROP age_range, DROP gender, DROP gender_other');
    }
}
