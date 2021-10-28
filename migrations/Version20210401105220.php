<?php

namespace Migrations;

use App\Entity\Coalition\Cause;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210401105220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          cause
        CHANGE description description LONGTEXT DEFAULT NULL,
        ADD second_coalition_id INT UNSIGNED DEFAULT NULL,
        ADD status VARCHAR(255) DEFAULT NULL');

        $this->addSql(sprintf("UPDATE cause SET status = '%s'", Cause::STATUS_PENDING));

        $this->addSql('ALTER TABLE cause CHANGE status status VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBF38C2B2DC FOREIGN KEY (second_coalition_id) REFERENCES coalition (id)');
        $this->addSql('CREATE INDEX IDX_F0DA7FBF38C2B2DC ON cause (second_coalition_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBF38C2B2DC');
        $this->addSql('DROP INDEX IDX_F0DA7FBF38C2B2DC ON cause');
        $this->addSql('ALTER TABLE cause DROP second_coalition_id, DROP status, CHANGE description description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
