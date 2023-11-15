<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210910101222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT FK_2C3FCFFB8AAB0BD7');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT FK_2C3FCFFB37BC4DC6');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT FK_2C3FCFFB6ACE3B73');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT FK_2C3FCFFB604B8382');
        $this->addSql('ALTER TABLE abstract_notification ADD talk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB8AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB37BC4DC6 FOREIGN KEY (emitter_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB6ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB6F0601D5 ON abstract_notification (talk_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT FK_2C3FCFFB6F0601D5');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT fk_2c3fcffb8aab0bd7');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT fk_2c3fcffb37bc4dc6');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT fk_2c3fcffb6ace3b73');
        $this->addSql('ALTER TABLE abstract_notification DROP CONSTRAINT fk_2c3fcffb604b8382');
        $this->addSql('DROP INDEX IDX_2C3FCFFB6F0601D5');
        $this->addSql('ALTER TABLE abstract_notification DROP talk_id');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT fk_2c3fcffb8aab0bd7 FOREIGN KEY (submit_id) REFERENCES submit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT fk_2c3fcffb37bc4dc6 FOREIGN KEY (emitter_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT fk_2c3fcffb6ace3b73 FOREIGN KEY (participation_id) REFERENCES participation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT fk_2c3fcffb604b8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
