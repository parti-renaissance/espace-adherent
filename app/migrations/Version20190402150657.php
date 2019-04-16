<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190402150657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests 
        ADD 
          assessor_postal_code VARCHAR(15) NOT NULL, 
        ADD 
          assessor_country VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assessor_requests DROP assessor_postal_code, DROP assessor_country');
    }
}
