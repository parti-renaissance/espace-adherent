<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190419212941 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE referent_team_member (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          referent_id INT UNSIGNED NOT NULL, 
          UNIQUE INDEX UNIQ_6C0067125F06C53 (adherent_id), 
          INDEX IDX_6C0067135E47E35 (referent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C0067125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C0067135E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE referent_team');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE referent_team (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          referent_id INT UNSIGNED NOT NULL, 
          UNIQUE INDEX UNIQ_B8011BE025F06C53 (adherent_id), 
          INDEX IDX_B8011BE035E47E35 (referent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_team 
        ADD 
          CONSTRAINT FK_B8011BE025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_team 
        ADD 
          CONSTRAINT FK_B8011BE035E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE referent_team_member');
    }
}
