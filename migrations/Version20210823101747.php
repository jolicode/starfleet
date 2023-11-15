<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210823101747 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE submit ADD submitted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE submit ADD CONSTRAINT FK_3F31B34379F7D87D FOREIGN KEY (submitted_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3F31B34379F7D87D ON submit (submitted_by_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F24D5BB2B36786B ON talk (title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE submit DROP CONSTRAINT FK_3F31B34379F7D87D');
        $this->addSql('DROP INDEX IDX_3F31B34379F7D87D');
        $this->addSql('ALTER TABLE submit DROP submitted_by_id');
        $this->addSql('DROP INDEX UNIQ_9F24D5BB2B36786B');
    }
}
