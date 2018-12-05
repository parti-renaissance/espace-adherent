<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181205150858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_1858998825F06C53');
        $this->addSql('DROP INDEX IDX_1858998825F06C53 ON ideas_workshop_comment');
        $this->addSql('ALTER TABLE ideas_workshop_comment CHANGE adherent_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_18589988F675F31B ON ideas_workshop_comment (author_id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7225F06C53');
        $this->addSql('DROP INDEX IDX_CA001C7225F06C53 ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea CHANGE adherent_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C72F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_CA001C72F675F31B ON ideas_workshop_idea (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988F675F31B');
        $this->addSql('DROP INDEX IDX_18589988F675F31B ON ideas_workshop_comment');
        $this->addSql('ALTER TABLE ideas_workshop_comment CHANGE author_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_1858998825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_1858998825F06C53 ON ideas_workshop_comment (adherent_id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C72F675F31B');
        $this->addSql('DROP INDEX IDX_CA001C72F675F31B ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea CHANGE author_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_CA001C7225F06C53 ON ideas_workshop_idea (adherent_id)');
    }
}
