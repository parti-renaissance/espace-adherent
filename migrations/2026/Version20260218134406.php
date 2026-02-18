<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260218134406 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94FAF04979');
        $this->addSql('DROP INDEX IDX_28CA9F94FAF04979 ON adherent_message_filters');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                ADD
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                DROP
                  adherent_segment_id,
                CHANGE
                  dtype dtype VARCHAR(255) DEFAULT NULL,
                CHANGE
                  postal_code postal_code VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28CA9F94D17F50A6 ON adherent_message_filters (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_28CA9F94D17F50A6 ON adherent_message_filters');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                ADD
                  adherent_segment_id INT UNSIGNED DEFAULT NULL,
                DROP
                  uuid,
                CHANGE
                  postal_code postal_code VARCHAR(10) DEFAULT NULL,
                CHANGE
                  dtype dtype VARCHAR(255) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                ADD
                  CONSTRAINT FK_28CA9F94FAF04979 FOREIGN KEY (adherent_segment_id) REFERENCES adherent_segment (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_28CA9F94FAF04979 ON adherent_message_filters (adherent_segment_id)');
    }
}
