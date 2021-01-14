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

final class Version20210114153636 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE fetcher_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fetcher_configuration (id INT NOT NULL, fetcher_class VARCHAR(255) NOT NULL, configuration JSONB NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF4B8E82B74EDEDA ON fetcher_configuration (fetcher_class)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE fetcher_configuration_id_seq CASCADE');
        $this->addSql('DROP TABLE fetcher_configuration');
        $this->addSql('DROP INDEX UNIQ_EF4B8E82B74EDEDA');
    }
}
