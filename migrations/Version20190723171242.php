<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190723171242 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE municipal_event ADD category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          municipal_event 
        ADD 
          CONSTRAINT FK_2A5B42D12469DE2 FOREIGN KEY (category_id) REFERENCES events_categories (id)');
        $this->addSql('CREATE INDEX IDX_2A5B42D12469DE2 ON municipal_event (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE municipal_event DROP FOREIGN KEY FK_2A5B42D12469DE2');
        $this->addSql('DROP INDEX IDX_2A5B42D12469DE2 ON municipal_event');
        $this->addSql('ALTER TABLE municipal_event DROP category_id');
    }
}
