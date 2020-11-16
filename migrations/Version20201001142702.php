<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201001142702 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          ocr_payload JSON DEFAULT NULL, 
        ADD 
          ocr_status VARCHAR(255) DEFAULT NULL, 
        ADD 
          ocr_result VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE certification_request DROP ocr_payload, DROP ocr_status, DROP ocr_result');
    }
}
