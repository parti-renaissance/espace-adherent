<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221215150113 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        ADD
          seats SMALLINT UNSIGNED DEFAULT NULL,
        ADD
          majority_prime SMALLINT UNSIGNED DEFAULT NULL,
        ADD
          majority_prime_round_sup_mode TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP seats, DROP majority_prime, DROP majority_prime_round_sup_mode');
    }
}
