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

final class Version20210112170750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excluded_tag RENAME TO conference_filter');
        $this->addSql('DROP SEQUENCE excluded_tag_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE conference_filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference_filter RENAME TO excluded_tag');
        $this->addSql('DROP SEQUENCE conference_filter_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE excluded_tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }
}
