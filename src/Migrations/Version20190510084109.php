<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190510084109 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE conference_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE submit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE participation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE talk_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conference (id INT NOT NULL, remote_id VARCHAR(255) DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, source VARCHAR(20) NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, location VARCHAR(255) NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cfp_url VARCHAR(255) DEFAULT NULL, cfp_end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, site_url VARCHAR(255) DEFAULT NULL, article_url TEXT DEFAULT NULL, attended BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_911533C8D1B862B8 ON conference (hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_911533C8989D9B62 ON conference (slug)');
        $this->addSql('CREATE TABLE conferences_tags (conference_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(conference_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_48E3E609604B8382 ON conferences_tags (conference_id)');
        $this->addSql('CREATE INDEX IDX_48E3E609BAD26311 ON conferences_tags (tag_id)');
        $this->addSql('CREATE TABLE submit (id INT NOT NULL, conference_id INT DEFAULT NULL, talk_id INT DEFAULT NULL, submitted_at DATE NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F31B343604B8382 ON submit (conference_id)');
        $this->addSql('CREATE INDEX IDX_3F31B3436F0601D5 ON submit (talk_id)');
        $this->addSql('CREATE TABLE submits_users (submit_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(submit_id, user_id))');
        $this->addSql('CREATE INDEX IDX_B2218AE08AAB0BD7 ON submits_users (submit_id)');
        $this->addSql('CREATE INDEX IDX_B2218AE0A76ED395 ON submits_users (user_id)');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, name VARCHAR(255) NOT NULL, selected BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('CREATE TABLE participation (id INT NOT NULL, conference_id INT NOT NULL, participant_id INT NOT NULL, as_speaker BOOLEAN NOT NULL, need_transport BOOLEAN NOT NULL, need_hotel BOOLEAN NOT NULL, need_ticket BOOLEAN NOT NULL, marking JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB55E24F604B8382 ON participation (conference_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F9D1C3019 ON participation (participant_id)');
        $this->addSql('COMMENT ON COLUMN participation.marking IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, google_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, bio TEXT DEFAULT NULL, roles JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE talk (id INT NOT NULL, title VARCHAR(255) NOT NULL, intro TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conferences_tags ADD CONSTRAINT FK_48E3E609BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B343604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B3436F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE08AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F9D1C3019 FOREIGN KEY (participant_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conferences_tags DROP CONSTRAINT FK_48E3E609604B8382');
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT FK_3F31B343604B8382');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24F604B8382');
        $this->addSql('ALTER TABLE submits_users DROP CONSTRAINT FK_B2218AE08AAB0BD7');
        $this->addSql('ALTER TABLE conferences_tags DROP CONSTRAINT FK_48E3E609BAD26311');
        $this->addSql('ALTER TABLE submits_users DROP CONSTRAINT FK_B2218AE0A76ED395');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24F9D1C3019');
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT FK_3F31B3436F0601D5');
        $this->addSql('DROP SEQUENCE conference_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE submit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE participation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE talk_id_seq CASCADE');
        $this->addSql('DROP TABLE conference');
        $this->addSql('DROP TABLE conferences_tags');
        $this->addSql('DROP TABLE submit');
        $this->addSql('DROP TABLE submits_users');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE talk');
    }
}
