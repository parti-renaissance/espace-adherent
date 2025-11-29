<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230330153931 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD supervisor_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C619E9AC5F FOREIGN KEY (supervisor_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_A36198C619E9AC5F ON committees (supervisor_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C619E9AC5F');
        $this->addSql('DROP INDEX IDX_A36198C619E9AC5F ON committees');
        $this->addSql('ALTER TABLE committees DROP supervisor_id');
    }
}
