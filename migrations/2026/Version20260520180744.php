<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520180744 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_source
                ADD
                  confirmation_email_template_id INT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_source
                ADD
                  CONSTRAINT FK_87DDA8D8EEEE137 FOREIGN KEY (confirmation_email_template_id) REFERENCES transactional_email_template (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_87DDA8D8EEEE137 ON renaissance_newsletter_source (confirmation_email_template_id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE renaissance_newsletter_source DROP FOREIGN KEY FK_87DDA8D8EEEE137');
        $this->addSql('DROP INDEX IDX_87DDA8D8EEEE137 ON renaissance_newsletter_source');
        $this->addSql('ALTER TABLE renaissance_newsletter_source DROP confirmation_email_template_id');
    }
}
