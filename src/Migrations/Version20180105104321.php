<?php

declare(strict_types=1);

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180105104321 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conference (id INT AUTO_INCREMENT NOT NULL, remote_id VARCHAR(255) DEFAULT NULL, source VARCHAR(20) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, cfp_url VARCHAR(255) DEFAULT NULL, cfp_end_at DATETIME DEFAULT NULL, site_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_911533C8989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conferences_tags (conference_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_48E3E609604B8382 (conference_id), INDEX IDX_48E3E609BAD26311 (tag_id), PRIMARY KEY(conference_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE talk (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, intro LONGTEXT NOT NULL, submitted_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE talks_users (talk_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DE7613EE6F0601D5 (talk_id), INDEX IDX_DE7613EEA76ED395 (user_id), PRIMARY KEY(talk_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE talks_conferences (talk_id INT NOT NULL, conference_id INT NOT NULL, INDEX IDX_2122A8B56F0601D5 (talk_id), INDEX IDX_2122A8B5604B8382 (conference_id), PRIMARY KEY(talk_id, conference_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, google_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT FK_DE7613EE6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT FK_DE7613EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_conferences ADD CONSTRAINT FK_2122A8B56F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE talks_conferences ADD CONSTRAINT FK_2122A8B5604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conferences_tags DROP FOREIGN KEY FK_48E3E609604B8382');
        $this->addSql('ALTER TABLE talks_conferences DROP FOREIGN KEY FK_2122A8B5604B8382');
        $this->addSql('ALTER TABLE talks_users DROP FOREIGN KEY FK_DE7613EE6F0601D5');
        $this->addSql('ALTER TABLE talks_conferences DROP FOREIGN KEY FK_2122A8B56F0601D5');
        $this->addSql('ALTER TABLE conferences_tags DROP FOREIGN KEY FK_48E3E609BAD26311');
        $this->addSql('ALTER TABLE talks_users DROP FOREIGN KEY FK_DE7613EEA76ED395');
        $this->addSql('DROP TABLE conference');
        $this->addSql('DROP TABLE conferences_tags');
        $this->addSql('DROP TABLE talk');
        $this->addSql('DROP TABLE talks_users');
        $this->addSql('DROP TABLE talks_conferences');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
    }
}
