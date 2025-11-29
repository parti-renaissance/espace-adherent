<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240624143855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_4D04EBA47B00651C ON procuration_v2_proxies (status)');
        $this->addSql('CREATE INDEX IDX_4D04EBA48B8E8428 ON procuration_v2_proxies (created_at)');
        $this->addSql('CREATE INDEX IDX_F6D458CB7B00651C ON procuration_v2_requests (status)');
        $this->addSql('CREATE INDEX IDX_F6D458CB8B8E8428 ON procuration_v2_requests (created_at)');

        $this->addSql('CREATE INDEX IDX_8750906810DBBEC4 ON procuration_v2_proxy_slot (manual)');
        $this->addSql('CREATE INDEX IDX_DA56A35F10DBBEC4 ON procuration_v2_request_slot (manual)');

        $this->addSql('CREATE INDEX IDX_A2DDD28AA9E377A ON procuration_v2_rounds (date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_4D04EBA47B00651C ON procuration_v2_proxies');
        $this->addSql('DROP INDEX IDX_4D04EBA48B8E8428 ON procuration_v2_proxies');
        $this->addSql('DROP INDEX IDX_F6D458CB7B00651C ON procuration_v2_requests');
        $this->addSql('DROP INDEX IDX_F6D458CB8B8E8428 ON procuration_v2_requests');

        $this->addSql('DROP INDEX IDX_8750906810DBBEC4 ON procuration_v2_proxy_slot');
        $this->addSql('DROP INDEX IDX_DA56A35F10DBBEC4 ON procuration_v2_request_slot');

        $this->addSql('DROP INDEX IDX_A2DDD28AA9E377A ON procuration_v2_rounds');
    }
}
