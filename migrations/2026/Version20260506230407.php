<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506230407 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mailchimp_static_segment_member (id INT UNSIGNED AUTO_INCREMENT NOT NULL, static_segment_id INT UNSIGNED NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, chunk_number INT UNSIGNED DEFAULT 0 NOT NULL, processing_status VARCHAR(255) DEFAULT \'pending\' NOT NULL, processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_18BF462A25F06C53 (adherent_id), INDEX IDX_18BF462AF8DF7CF6 (static_segment_id), INDEX IDX_18BF462AF8DF7CF635818044 (static_segment_id, chunk_number), INDEX IDX_18BF462AF8DF7CF6487D0A4C (static_segment_id, processing_status), UNIQUE INDEX UNIQ_18BF462A25F06C53F8DF7CF6 (adherent_id, static_segment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mailchimp_static_segment_member ADD CONSTRAINT FK_18BF462AF8DF7CF6 FOREIGN KEY (static_segment_id) REFERENCES mailchimp_static_segment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailchimp_static_segment_member ADD CONSTRAINT FK_18BF462A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE adherent_message_targeted DROP FOREIGN KEY FK_646FE8E325F06C53');
        $this->addSql('ALTER TABLE adherent_message_targeted DROP FOREIGN KEY FK_646FE8E3537A1329');
        $this->addSql('DROP TABLE adherent_message_targeted');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD preparation_locked_by_id INT UNSIGNED DEFAULT NULL, DROP preparation_locked_by, DROP preparation_failure_detail');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD CONSTRAINT FK_CFABD309708F43EA FOREIGN KEY (preparation_locked_by_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_CFABD309708F43EA ON mailchimp_campaign (preparation_locked_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_message_targeted (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_id INT UNSIGNED NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, targeted_at DATETIME NOT NULL, chunk_number INT UNSIGNED DEFAULT 0 NOT NULL, processing_status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pending\' NOT NULL COLLATE `utf8mb4_unicode_ci`, processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', error_message LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_646FE8E325F06C53 (adherent_id), INDEX IDX_646FE8E3537A1329 (message_id), INDEX IDX_646FE8E3537A132935818044 (message_id, chunk_number), INDEX IDX_646FE8E3537A1329487D0A4C (message_id, processing_status), UNIQUE INDEX UNIQ_646FE8E325F06C53537A1329 (adherent_id, message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE adherent_message_targeted ADD CONSTRAINT FK_646FE8E325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE adherent_message_targeted ADD CONSTRAINT FK_646FE8E3537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailchimp_static_segment_member DROP FOREIGN KEY FK_18BF462AF8DF7CF6');
        $this->addSql('ALTER TABLE mailchimp_static_segment_member DROP FOREIGN KEY FK_18BF462A25F06C53');
        $this->addSql('DROP TABLE mailchimp_static_segment_member');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD309708F43EA');
        $this->addSql('DROP INDEX IDX_CFABD309708F43EA ON mailchimp_campaign');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD preparation_locked_by VARCHAR(255) DEFAULT NULL, ADD preparation_failure_detail LONGTEXT DEFAULT NULL, DROP preparation_locked_by_id');
    }
}
