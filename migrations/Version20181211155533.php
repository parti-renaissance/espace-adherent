<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181211155533 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_vote (id INT UNSIGNED AUTO_INCREMENT NOT NULL, idea_id INT UNSIGNED NOT NULL, adherent_id INT UNSIGNED NOT NULL, type VARCHAR(10) NOT NULL, INDEX IDX_9A9B53535B6FEF7D (idea_id), INDEX IDX_9A9B535325F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B53535B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id)');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B535325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_1858998825F06C53');
        $this->addSql('DROP INDEX IDX_1858998825F06C53 ON ideas_workshop_comment');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD author_id INT UNSIGNED NOT NULL, DROP adherent_id');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_18589988F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_18589988F675F31B ON ideas_workshop_comment (author_id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7225F06C53');
        $this->addSql('DROP INDEX IDX_CA001C7225F06C53 ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD with_committee TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE status status VARCHAR(11) DEFAULT \'DRAFT\' NOT NULL, CHANGE adherent_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C72F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_CA001C72F675F31B ON ideas_workshop_idea (author_id)');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP FOREIGN KEY FK_CE975BDDAA334807');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD author_id INT UNSIGNED NOT NULL, ADD content LONGTEXT NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, CHANGE answer_id answer_id INT NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD CONSTRAINT FK_CE975BDDF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD CONSTRAINT FK_CE975BDDAA334807 FOREIGN KEY (answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CE975BDDF675F31B ON ideas_workshop_thread (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ideas_workshop_vote');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP FOREIGN KEY FK_18589988F675F31B');
        $this->addSql('DROP INDEX IDX_18589988F675F31B ON ideas_workshop_comment');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD adherent_id INT UNSIGNED DEFAULT NULL, DROP author_id');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD CONSTRAINT FK_1858998825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_1858998825F06C53 ON ideas_workshop_comment (adherent_id)');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C72F675F31B');
        $this->addSql('DROP INDEX IDX_CA001C72F675F31B ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP with_committee, CHANGE status status VARCHAR(11) DEFAULT \'PENDING\' NOT NULL COLLATE utf8_unicode_ci, CHANGE author_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_CA001C7225F06C53 ON ideas_workshop_idea (adherent_id)');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP FOREIGN KEY FK_CE975BDDF675F31B');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP FOREIGN KEY FK_CE975BDDAA334807');
        $this->addSql('DROP INDEX IDX_CE975BDDF675F31B ON ideas_workshop_thread');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP author_id, DROP content, DROP created_at, DROP updated_at, DROP deleted_at, CHANGE answer_id answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD CONSTRAINT FK_CE975BDDAA334807 FOREIGN KEY (answer_id) REFERENCES ideas_workshop_answer (id)');
    }
}
