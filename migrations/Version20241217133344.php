<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241217133344 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          ohme_contact
        ADD
          payment_count INT DEFAULT NULL,
        ADD
          last_payment_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ohme_contact DROP payment_count, DROP last_payment_date');
    }
}
