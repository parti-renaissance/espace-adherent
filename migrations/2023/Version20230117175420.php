<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230117175420 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE local_election_substitute_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          election_id INT UNSIGNED NOT NULL,
          gender VARCHAR(255) NOT NULL,
          biography LONGTEXT DEFAULT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          position INT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) NOT NULL,
          faith_statement LONGTEXT DEFAULT NULL,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_BD11975AD17F50A6 (uuid),
          INDEX IDX_BD11975A25F06C53 (adherent_id),
          INDEX IDX_BD11975AFC1537C1 (candidacies_group_id),
          INDEX IDX_BD11975AA708DAFF (election_id),
          INDEX IDX_BD11975AE7927C74 (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          local_election_substitute_candidacy
        ADD
          CONSTRAINT FK_BD11975A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          local_election_substitute_candidacy
        ADD
          CONSTRAINT FK_BD11975AFC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES local_election_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          local_election_substitute_candidacy
        ADD
          CONSTRAINT FK_BD11975AA708DAFF FOREIGN KEY (election_id) REFERENCES local_election (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE local_election_substitute_candidacy');
    }
}
