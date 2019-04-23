<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190420010649 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C0067125F06C53');
        $this->addSql('DROP INDEX UNIQ_6C0067125F06C53 ON referent_team_member');
        $this->addSql('ALTER TABLE referent_team_member CHANGE adherent_id member_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C006717597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C006717597D3FE ON referent_team_member (member_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C006717597D3FE');
        $this->addSql('DROP INDEX UNIQ_6C006717597D3FE ON referent_team_member');
        $this->addSql('ALTER TABLE referent_team_member CHANGE member_id adherent_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C0067125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C0067125F06C53 ON referent_team_member (adherent_id)');
    }
}
