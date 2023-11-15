<?php

declare(strict_types=1);

namespace qa;

use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\parallel;

#[AsTask(description: 'Runs all QA tasks')]
function all(): int
{
    install();
    $cs = cs();
    $phpstan = phpstan();

    return max($cs, $phpstan);
}

#[AsTask(description: 'Installs tooling')]
function install(): void
{
    docker_compose_run('composer install -o', workDir: '/home/app/root/tools/php-cs-fixer');
    docker_compose_run('composer install -o', workDir: '/home/app/root/tools/phpstan');
}

#[AsTask(description: 'Runs PHPStan', aliases: ['phpstan'])]
function phpstan(): int
{
    return docker_exit_code('phpstan --configuration=/home/app/root/phpstan.neon', workDir: '/home/app/application');
}

#[AsTask(description: 'Fixes Coding Style', aliases: ['cs'])]
function cs(bool $dryRun = false): int
{
    if ($dryRun) {
        return docker_exit_code('php-cs-fixer fix --dry-run --diff', workDir: '/home/app/root');
    }

    return docker_exit_code('php-cs-fixer fix', workDir: '/home/app/root');
}

#[AsTask(name: 'twig-lint', description: 'Lint twig files')]
function twig_lint(): int
{
    return docker_exit_code('bin/console lint:twig --show-deprecations templates');
}

#[AsTask(name: 'yaml-lint', description: 'Lint YAML files')]
function yaml_lint(): int
{
    return docker_exit_code('bin/console lint:yaml config --parse-tags');
}

#[AsTask(description: 'Run PHPUnit tests', aliases: ['tests'])]
function phpunit(string $group = '', string $filter = ''): int
{
    $displayGroup = $group ?: 'all';

    io()->section("Running PHPUnit {$displayGroup} tests");

    $command = './vendor/bin/simple-phpunit';

    if ($group) {
        $command .= ' --group ' . $group;
    }

    if ($filter) {
        $command .= ' --filter ' . $filter;
    }

    return docker_exit_code($command);
}
