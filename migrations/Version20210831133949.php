<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210831133949 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, target_user_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, data jsonb NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CA6C066AFE ON notification (target_user_id)');
        $this->addSql('CREATE TABLE submit_added_notification (id INT NOT NULL, source_user_id INT NOT NULL, conference_id INT NOT NULL, talk_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, data jsonb NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_349A191FEEB16BFD ON submit_added_notification (source_user_id)');
        $this->addSql('CREATE INDEX IDX_349A191F604B8382 ON submit_added_notification (conference_id)');
        $this->addSql('CREATE INDEX IDX_349A191F6F0601D5 ON submit_added_notification (talk_id)');
        $this->addSql('CREATE TABLE submit_status_changed_notification (id INT NOT NULL, submit_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, data jsonb NOT NULL, new_status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_61F3D9018AAB0BD7 ON submit_status_changed_notification (submit_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit_added_notification ADD CONSTRAINT FK_349A191FEEB16BFD FOREIGN KEY (source_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit_added_notification ADD CONSTRAINT FK_349A191F604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit_added_notification ADD CONSTRAINT FK_349A191F6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submit_status_changed_notification ADD CONSTRAINT FK_61F3D9018AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE submit_added_notification');
        $this->addSql('DROP TABLE submit_status_changed_notification');
    }
}
