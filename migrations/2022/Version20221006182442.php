<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221006182442 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          password VARCHAR(255) DEFAULT NULL,
        ADD
          allow_email_notifications TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          allow_mobile_notifications TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        DROP
          password,
        DROP
          allow_email_notifications,
        DROP
          allow_mobile_notifications');
    }
}
