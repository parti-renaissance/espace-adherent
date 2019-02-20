<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180523105720 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE republican_silence (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                begin_at DATETIME NOT NULL, 
                finish_at DATETIME NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE republican_silence_referent_tag (
                republican_silence_id INT UNSIGNED NOT NULL, 
                referent_tag_id INT UNSIGNED NOT NULL, 
                INDEX IDX_543DED2612359909 (republican_silence_id), 
                INDEX IDX_543DED269C262DB3 (referent_tag_id), 
                PRIMARY KEY(republican_silence_id, referent_tag_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql('ALTER TABLE republican_silence_referent_tag ADD CONSTRAINT FK_543DED2612359909 FOREIGN KEY (republican_silence_id) REFERENCES republican_silence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE republican_silence_referent_tag ADD CONSTRAINT FK_543DED269C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5387574A3826374D ON events (begin_at)');
        $this->addSql('CREATE INDEX IDX_5387574AFE28FD87 ON events (finish_at)');
        $this->addSql('CREATE INDEX IDX_5387574A7B00651C ON events (status)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE republican_silence_referent_tag DROP FOREIGN KEY FK_543DED2612359909');
        $this->addSql('DROP TABLE republican_silence');
        $this->addSql('DROP TABLE republican_silence_referent_tag');
        $this->addSql('DROP INDEX IDX_5387574A3826374D ON events');
        $this->addSql('DROP INDEX IDX_5387574AFE28FD87 ON events');
        $this->addSql('DROP INDEX IDX_5387574A7B00651C ON events');
    }
}
