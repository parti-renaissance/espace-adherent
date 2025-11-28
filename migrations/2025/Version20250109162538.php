<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109162538 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC8071F7E88B');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80ED1A100B');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80F675F31B');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP FOREIGN KEY FK_D269D0AA6A24B1A2');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP FOREIGN KEY FK_D269D0AABEF808A3');
        $this->addSql('DROP TABLE committee_feed_item');
        $this->addSql('DROP TABLE committee_feed_item_user_documents');
        $this->addSql('ALTER TABLE adherent_messages DROP send_to_timeline');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          event_id INT UNSIGNED DEFAULT NULL,
          item_type VARCHAR(18) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            content LONGTEXT CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            created_at DATETIME NOT NULL,
            uuid CHAR(36) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
            published TINYINT(1) DEFAULT 1 NOT NULL,
            UNIQUE INDEX UNIQ_4F1CDC80D17F50A6 (uuid),
            INDEX IDX_4F1CDC8071F7E88B (event_id),
            INDEX IDX_4F1CDC80ED1A100B (committee_id),
            INDEX IDX_4F1CDC80F675F31B (author_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE committee_feed_item_user_documents (
          committee_feed_item_id INT UNSIGNED NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_D269D0AA6A24B1A2 (user_document_id),
          INDEX IDX_D269D0AABEF808A3 (committee_feed_item_id),
          PRIMARY KEY(
            committee_feed_item_id, user_document_id
          )
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC8071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON
        UPDATE
          NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          committee_feed_item_user_documents
        ADD
          CONSTRAINT FK_D269D0AA6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_feed_item_user_documents
        ADD
          CONSTRAINT FK_D269D0AABEF808A3 FOREIGN KEY (committee_feed_item_id) REFERENCES committee_feed_item (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_messages ADD send_to_timeline TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
