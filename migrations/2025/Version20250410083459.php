<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410083459 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_activation_code ADD revoked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE status status VARCHAR(10) DEFAULT \'PENDING\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_activation_code DROP revoked_at');
        $this->addSql('ALTER TABLE adherents CHANGE status status VARCHAR(10) DEFAULT \'DISABLED\' NOT NULL');
    }
}
