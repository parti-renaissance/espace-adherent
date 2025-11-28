<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240621152825 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          matcher_id INT UNSIGNED DEFAULT NULL,
        ADD
          matched_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_87509068F38CBA7C FOREIGN KEY (matcher_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_87509068F38CBA7C ON procuration_v2_proxy_slot (matcher_id)');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          matcher_id INT UNSIGNED DEFAULT NULL,
        ADD
          matched_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35FF38CBA7C FOREIGN KEY (matcher_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_DA56A35FF38CBA7C ON procuration_v2_request_slot (matcher_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_87509068F38CBA7C');
        $this->addSql('DROP INDEX IDX_87509068F38CBA7C ON procuration_v2_proxy_slot');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP matcher_id, DROP matched_at');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35FF38CBA7C');
        $this->addSql('DROP INDEX IDX_DA56A35FF38CBA7C ON procuration_v2_request_slot');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP matcher_id, DROP matched_at');
    }
}
