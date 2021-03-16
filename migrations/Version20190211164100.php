<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190211164100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD comments_count INT UNSIGNED NOT NULL DEFAULT 0');
        $this->addSql(
            <<<SQL
            UPDATE ideas_workshop_idea AS idea
            INNER JOIN (
                SELECT idea.id, COUNT(1) AS total
                FROM ideas_workshop_idea AS idea
                INNER JOIN ideas_workshop_answer AS answer ON answer.idea_id = idea.id
                INNER JOIN ideas_workshop_thread AS thread ON thread.answer_id = answer.id AND thread.deleted_at IS NULL
                INNER JOIN ideas_workshop_comment AS comment ON comment.thread_id = thread.id AND comment.enabled AND comment.deleted_at IS NULL
                GROUP BY idea.id
            ) AS count ON count.id = idea.id
            SET idea.comments_count = idea.comments_count + count.total
SQL
        );

        $this->addSql(
            <<<SQL
            UPDATE ideas_workshop_idea AS idea
            INNER JOIN (
                SELECT idea.id, COUNT(1) AS total
                FROM ideas_workshop_idea AS idea
                INNER JOIN ideas_workshop_answer AS answer ON answer.idea_id = idea.id
                INNER JOIN ideas_workshop_thread AS thread ON thread.answer_id = answer.id AND thread.deleted_at IS NULL AND thread.enabled
                GROUP BY idea.id
            ) AS count ON count.id = idea.id
            SET idea.comments_count = idea.comments_count + count.total
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP comments_count');
    }
}
