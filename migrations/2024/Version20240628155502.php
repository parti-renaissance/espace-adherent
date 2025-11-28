<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240628155502 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_initial_requests ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_initial_requests
        ADD
          CONSTRAINT FK_4BF1190625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_4BF1190625F06C53 ON procuration_v2_initial_requests (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_initial_requests DROP FOREIGN KEY FK_4BF1190625F06C53');
        $this->addSql('DROP INDEX IDX_4BF1190625F06C53 ON procuration_v2_initial_requests');
        $this->addSql('ALTER TABLE procuration_v2_initial_requests DROP adherent_id');
    }
}
