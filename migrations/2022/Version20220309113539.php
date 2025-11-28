<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220309113539 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE assessor_requests SET voter_number = :voter_number WHERE voter_number IS NULL', ['voter_number' => '00000']);
        $this->addSql('ALTER TABLE
          assessor_requests
        DROP
          birth_name,
        CHANGE
          office_number office_number VARCHAR(10) DEFAULT NULL,
        CHANGE
          voter_number voter_number VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          assessor_requests
        ADD
          birth_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          office_number office_number VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          voter_number voter_number VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
