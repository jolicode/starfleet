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

final class Version20210604120827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE participation ALTER marking DROP DEFAULT');
        $this->addSql('ALTER TABLE participation ALTER transport_status DROP DEFAULT');
        $this->addSql('ALTER TABLE participation ALTER hotel_status DROP DEFAULT');
        $this->addSql('ALTER TABLE participation ALTER conference_ticket_status DROP DEFAULT');
        $this->addSql('ALTER TABLE participation ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE participation ALTER updated_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE participation ALTER transport_status SET DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ALTER hotel_status SET DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ALTER conference_ticket_status SET DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ALTER marking SET DEFAULT \'pending\'');
        $this->addSql('ALTER TABLE participation ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE participation ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
    }
}
