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

use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\io;
use function Castor\notify;
use function Castor\variable;

import(__DIR__ . '/.castor');

/**
 * @return array<string, mixed>
 */
function create_default_variables(): array
{
    $projectName = 'starfleet';
    $tld = 'app';

    return [
        'project_name' => $projectName,
        'root_domain' => "{$projectName}.{$tld}",
        'extra_domains' => [
            "local.{$projectName}.{$tld}",
        ],
        'project_directory' => '.',
        'php_version' => $_SERVER['DS_PHP_VERSION'] ?? '8.1',
    ];
}

#[AsTask(description: 'Builds and starts the infrastructure, then install the application (composer, yarn, ...)')]
function start(): void
{
    infra\workers_stop();
    infra\generate_certificates();
    infra\build();
    infra\up();
    cache_clear();
    install();
    migrate();
    infra\workers_start();

    notify('The stack is now up and running.');
    io()->success('The stack is now up and running.');

    about();
}

#[AsTask(namespace: 'app', description: 'Installs the application (composer, yarn, ...)', aliases: ['install'])]
function install(): void
{
    $basePath = sprintf('%s/%s', variable('root_dir'), variable('project_directory'));

    if (is_file("{$basePath}/composer.json")) {
        docker_compose_run('composer install -n --prefer-dist --optimize-autoloader');
    }

    front_install();
    front_build();

    qa\install();
}

#[AsTask(name: 'install', namespace: 'app:front', description: 'Install the frontend dependencies')]
function front_install(): void
{
    docker_compose_run('yarn install');
}

#[AsTask(name: 'build', namespace: 'app:front', description: 'Build the frontend')]
function front_build(): void
{
    docker_compose_run('yarn run build');
}

#[AsTask(name: 'watch', namespace: 'app:front', description: 'Watch the frontend')]
function front_watch(): void
{
    docker_compose_run('yarn run watch');
}

#[AsTask(namespace: 'app', description: 'Clear the application cache', aliases: ['cache-clear'])]
function cache_clear(): void
{
    docker_compose_run('rm -rf var/cache/ && bin/console cache:warmup');
}

#[AsTask(namespace: 'app:db', description: 'Migrates database schema', aliases: ['migrate'])]
function migrate(): void
{
    docker_compose_run('bin/console doctrine:database:create --if-not-exists');
    docker_compose_run('bin/console doctrine:migration:migrate -n --allow-no-migration');
}

#[AsTask(namespace: 'app:db', description: 'Load fixtures in database', aliases: ['fixtures'])]
function fixtures(): void
{
    docker_compose_run('bin/console doctrine:database:drop --force');
    migrate();
    docker_compose_run('bin/console doctrine:fixtures:load -n');
}
