<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190610155709 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE submit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE participation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE submit (id INT NOT NULL, conference_id INT DEFAULT NULL, talk_id INT DEFAULT NULL, submitted_at DATE NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F31B343604B8382 ON submit (conference_id)');
        $this->addSql('CREATE INDEX IDX_3F31B3436F0601D5 ON submit (talk_id)');
        $this->addSql('CREATE TABLE submits_users (submit_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(submit_id, user_id))');
        $this->addSql('CREATE INDEX IDX_B2218AE08AAB0BD7 ON submits_users (submit_id)');
        $this->addSql('CREATE INDEX IDX_B2218AE0A76ED395 ON submits_users (user_id)');
        $this->addSql('CREATE TABLE participation (id INT NOT NULL, conference_id INT NOT NULL, participant_id INT NOT NULL, as_speaker BOOLEAN NOT NULL, need_transport BOOLEAN NOT NULL, need_hotel BOOLEAN NOT NULL, need_ticket BOOLEAN NOT NULL, marking JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB55E24F604B8382 ON participation (conference_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F9D1C3019 ON participation (participant_id)');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B343604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B3436F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE08AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submits_users ADD CONSTRAINT FK_B2218AE0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F9D1C3019 FOREIGN KEY (participant_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE talks_users');
        $this->addSql('ALTER TABLE conference ADD hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conference ADD article_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE conference ADD attended BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE conference ALTER id TYPE INT');
        $this->addSql('ALTER TABLE conference ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER source DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER slug DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER location DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER start_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER start_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER end_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER end_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER end_at DROP NOT NULL');
        $this->addSql('ALTER TABLE conference ALTER cfp_end_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER cfp_end_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER updated_at DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_911533C8D1B862B8 ON conference (hash)');
        $this->addSql('ALTER TABLE conferences_tags ALTER conference_id TYPE INT');
        $this->addSql('ALTER TABLE conferences_tags ALTER conference_id DROP DEFAULT');
        $this->addSql('ALTER TABLE conferences_tags ALTER tag_id TYPE INT');
        $this->addSql('ALTER TABLE conferences_tags ALTER tag_id DROP DEFAULT');
        $this->addSql('ALTER TABLE tag ADD selected BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ALTER id TYPE INT');
        $this->addSql('ALTER TABLE tag ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE tag ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE tag ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tag ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE tag ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tag ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL DEFAULT \'["ROLE_ADMIN"]\'');
        $this->addSql('ALTER TABLE users ALTER id TYPE INT');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE talk DROP CONSTRAINT fk_9f24d5bb604b8382');
        $this->addSql('ALTER TABLE talk DROP conference_id');
        $this->addSql('ALTER TABLE talk DROP submitted_at');
        $this->addSql('ALTER TABLE talk DROP status');
        $this->addSql('ALTER TABLE talk ALTER id TYPE INT');
        $this->addSql('ALTER TABLE talk ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ALTER title DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE talk ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE talk ALTER updated_at DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA starfleet');
        $this->addSql('ALTER TABLE submits_users DROP CONSTRAINT FK_B2218AE08AAB0BD7');
        $this->addSql('DROP SEQUENCE submit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE participation_id_seq CASCADE');
        $this->addSql('CREATE TABLE talks_users (talk_id BIGINT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(talk_id, user_id))');
        $this->addSql('CREATE INDEX idx_17049_idx_de7613ee6f0601d5 ON talks_users (talk_id)');
        $this->addSql('CREATE INDEX idx_17049_idx_de7613eea76ed395 ON talks_users (user_id)');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT fk_de7613ee6f0601d5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE talks_users ADD CONSTRAINT fk_de7613eea76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE submit');
        $this->addSql('DROP TABLE submits_users');
        $this->addSql('DROP TABLE participation');
        $this->addSql('ALTER TABLE tag DROP selected');
        $this->addSql('ALTER TABLE tag ALTER id TYPE BIGINT');
        $this->addSql('CREATE SEQUENCE tag_id_seq');
        $this->addSql('SELECT setval(\'tag_id_seq\', (SELECT MAX(id) FROM tag))');
        $this->addSql('ALTER TABLE tag ALTER id SET DEFAULT nextval(\'tag_id_seq\')');
        $this->addSql('ALTER TABLE tag ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE tag ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE tag ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE tag ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER INDEX uniq_389b7835e237e06 RENAME TO idx_17034_uniq_389b7835e237e06');
        $this->addSql('ALTER TABLE conferences_tags ALTER conference_id TYPE BIGINT');
        $this->addSql('ALTER TABLE conferences_tags ALTER conference_id DROP DEFAULT');
        $this->addSql('ALTER TABLE conferences_tags ALTER tag_id TYPE BIGINT');
        $this->addSql('ALTER TABLE conferences_tags ALTER tag_id DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_48e3e609bad26311 RENAME TO idx_17026_idx_48e3e609bad26311');
        $this->addSql('ALTER INDEX idx_48e3e609604b8382 RENAME TO idx_17026_idx_48e3e609604b8382');
        $this->addSql('DROP INDEX UNIQ_911533C8D1B862B8');
        $this->addSql('ALTER TABLE conference DROP hash');
        $this->addSql('ALTER TABLE conference DROP article_url');
        $this->addSql('ALTER TABLE conference DROP attended');
        $this->addSql('ALTER TABLE conference ALTER id TYPE BIGINT');
        $this->addSql('CREATE SEQUENCE conference_id_seq');
        $this->addSql('SELECT setval(\'conference_id_seq\', (SELECT MAX(id) FROM conference))');
        $this->addSql('ALTER TABLE conference ALTER id SET DEFAULT nextval(\'conference_id_seq\')');
        $this->addSql('ALTER TABLE conference ALTER source SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE conference ALTER slug SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE conference ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE conference ALTER location SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE conference ALTER start_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER start_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER end_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER end_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER end_at SET NOT NULL');
        $this->addSql('ALTER TABLE conference ALTER cfp_end_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER cfp_end_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE conference ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE conference ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER INDEX uniq_911533c8989d9b62 RENAME TO idx_17012_uniq_911533c8989d9b62');
        $this->addSql('ALTER TABLE talk ADD conference_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE talk ADD submitted_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE talk ADD status VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE talk ALTER id TYPE BIGINT');
        $this->addSql('CREATE SEQUENCE talk_id_seq');
        $this->addSql('SELECT setval(\'talk_id_seq\', (SELECT MAX(id) FROM talk))');
        $this->addSql('ALTER TABLE talk ALTER id SET DEFAULT nextval(\'talk_id_seq\')');
        $this->addSql('ALTER TABLE talk ALTER title SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE talk ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE talk ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE talk ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE talk ADD CONSTRAINT fk_9f24d5bb604b8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON UPDATE RESTRICT ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_17040_idx_9f24d5bb604b8382 ON talk (conference_id)');
        $this->addSql('ALTER TABLE users DROP roles');
        $this->addSql('ALTER TABLE users ALTER id TYPE BIGINT');
        $this->addSql('CREATE SEQUENCE users_id_seq');
        $this->addSql('SELECT setval(\'users_id_seq\', (SELECT MAX(id) FROM users))');
        $this->addSql('ALTER TABLE users ALTER id SET DEFAULT nextval(\'users_id_seq\')');
        $this->addSql('ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER INDEX uniq_1483a5e9e7927c74 RENAME TO idx_17054_uniq_8d93d649e7927c74');
    }
}
