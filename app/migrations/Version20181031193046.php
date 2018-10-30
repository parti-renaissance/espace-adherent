<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181031193046 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mailer_templates DROP FOREIGN KEY FK_46364D83A2C84DEF');
        $this->addSql('DROP INDEX UNIQ_46364D83A2C84DEF ON mailer_templates');
        $this->addSql('ALTER TABLE mailer_templates DROP last_version_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mailer_templates ADD last_version_id INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE mailer_templates ADD CONSTRAINT FK_46364D83A2C84DEF FOREIGN KEY (last_version_id) REFERENCES mailer_template_versions (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_46364D83A2C84DEF ON mailer_templates (last_version_id)');
    }
}
