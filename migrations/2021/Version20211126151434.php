<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126151434 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_address
        ADD
          postal_codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
        ADD
          voters_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_address DROP postal_codes, DROP voters_count');
    }
}
