<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210902135044 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, target_user_id INT NOT NULL, submit_id INT, emitter_id INT, participation_id INT, conference_id INT, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, read BOOLEAN NOT NULL DEFAULT FALSE, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CA6C066AFE ON notification (target_user_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA8AAB0BD7 ON notification (submit_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA37BC4DC6 ON notification (emitter_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA6ACE3B73 ON notification (participation_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA604B8382 ON notification (conference_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA8AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA37BC4DC6 FOREIGN KEY (emitter_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP TABLE notification');
    }
}
