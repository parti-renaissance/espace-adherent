<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260624142547 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mandrill_fallback_chunk DROP FOREIGN KEY FK_EC1D829FF639F774');
        $this->addSql('DROP TABLE mandrill_fallback_chunk');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP mandrill_fallback_status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mandrill_fallback_chunk (id INT UNSIGNED AUTO_INCREMENT NOT NULL, chunk_number INT UNSIGNED NOT NULL, status VARCHAR(255) NOT NULL, sent_at DATETIME DEFAULT NULL, campaign_id INT UNSIGNED NOT NULL, INDEX IDX_EC1D829FF639F774 (campaign_id), UNIQUE INDEX UNIQ_EC1D829FF639F77435818044 (campaign_id, chunk_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE mandrill_fallback_chunk ADD CONSTRAINT FK_EC1D829FF639F774 FOREIGN KEY (campaign_id) REFERENCES mailchimp_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD mandrill_fallback_status VARCHAR(255) DEFAULT NULL');
    }
}
