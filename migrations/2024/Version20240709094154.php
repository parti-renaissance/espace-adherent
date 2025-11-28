<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240709094154 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        ADD
          alert_title VARCHAR(255) DEFAULT NULL,
        ADD
          alert_cta_label VARCHAR(255) DEFAULT NULL,
        ADD
          alert_description VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP alert_title, DROP alert_cta_label, DROP alert_description');
    }
}
