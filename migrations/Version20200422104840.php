<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200422104840 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE certification_request CHANGE updated_at processed_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE certification_request SET processed_at = NULL WHERE processed_at = created_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE certification_request CHANGE processed_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE certification_request SET updated_at = created_at WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE certification_request CHANGE updated_at updated_at DATETIME NOT NULL');
    }
}
