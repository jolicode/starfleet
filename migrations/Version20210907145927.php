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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210907145927 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE abstract_notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE abstract_notification (id INT NOT NULL, target_user_id INT NOT NULL, submit_id INT DEFAULT NULL, emitter_id INT DEFAULT NULL, participation_id INT DEFAULT NULL, conference_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, trigger VARCHAR(255) NOT NULL, read BOOLEAN NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB6C066AFE ON abstract_notification (target_user_id)');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB8AAB0BD7 ON abstract_notification (submit_id)');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB37BC4DC6 ON abstract_notification (emitter_id)');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB6ACE3B73 ON abstract_notification (participation_id)');
        $this->addSql('CREATE INDEX IDX_2C3FCFFB604B8382 ON abstract_notification (conference_id)');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB6C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB8AAB0BD7 FOREIGN KEY (submit_id) REFERENCES submit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB37BC4DC6 FOREIGN KEY (emitter_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB6ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_notification ADD CONSTRAINT FK_2C3FCFFB604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE abstract_notification_id_seq CASCADE');
        $this->addSql('DROP TABLE abstract_notification');
    }
}
