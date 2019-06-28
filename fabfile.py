from fabric.api import task, env, shell_env
from fabric.operations import local, _shell_escape, settings
from functools import wraps
from fabric.context_managers import quiet
from fabric.colors import green, yellow
import os
import re
from sys import platform


# This will be used to prefix all docker objects (network, images, containers)
env.project_name = 'starfleet'
# This is the host directory containing your PHP application
env.project_directory = '.'
# This will be all your domain name, separated with comma
env.project_hostnames = 'local.starfleet.app,mail.local.starfleet.app'


def with_builder(func):
    @wraps(func)
    def decorated(*args, **kwargs):
        compose_files = env.compose_files[:]
        env.compose_files += ['docker-compose.builder.yml']
        ret = func(*args, **kwargs)
        env.compose_files = compose_files

        return ret
    return decorated


@with_builder
def build():
    """
    Build the infrastructure
    """
    command = 'build'
    command += ' --build-arg PROJECT_NAME=%s' % env.project_name
    command += ' --build-arg USER_ID=%s' % env.user_id

    docker_compose(command)


@task
def up():
    """
    Ensure infrastructure is sync and running
    """
    build()
    docker_compose('up --remove-orphans -d')


@task
def start():
    """
    Be sure that everything is started and installed
    """
    if env.dinghy:
        machine_running = local('dinghy status', capture=True)
        if machine_running.splitlines()[0].strip() != 'VM: running':
            local('dinghy up --no-proxy')
            local('docker-machine ssh dinghy "echo \'nameserver 8.8.8.8\' | sudo tee -a /etc/resolv.conf && sudo /etc/init.d/docker restart"')

    up()
    cache_clear()
    install()
    migrate()

    print green('You can now browse:')
    for domain in env.project_hostnames.split(','):
        print yellow("* https://" + domain)


@task
@with_builder
def install():
    """
    Install frontend application (composer, yarn, assets)
    """
    docker_compose_run('COMPOSER_MEMORY_LIMIT=-1 composer install -n --prefer-dist --optimize-autoloader')
    docker_compose_run('yarn')


@task
@with_builder
def upgrade():
    """
    Upgrade application dependencies (composer only)
    """
    docker_compose_run('COMPOSER_MEMORY_LIMIT=-1 composer update -n')


@task
@with_builder
def cache_clear():
    """
    Clear cache of the frontend application
    """
    docker_compose_run('rm -rf var/cache/', no_deps=True)


@task
@with_builder
def reset():
    """
    Reset database
    """
    docker_compose_run('bin/console doctrine:database:drop --if-exists --force', no_deps=True)
    docker_compose_run('bin/console doctrine:database:create --if-not-exists', no_deps=True)
    docker_compose_run('bin/console doctrine:migration:migrate --no-interaction', no_deps=True)


@task
@with_builder
def migrate():
    """
    Migrate database schema
    """
    docker_compose_run('bin/console doctrine:database:create --if-not-exists', no_deps=True)
    docker_compose_run('bin/console doctrine:migration:migrate --no-interaction', no_deps=True)


@task
@with_builder
def migration():
    """
    Generate a diff migration
    """
    docker_compose_run('bin/console make:migration --no-interaction', no_deps=True)


@task
@with_builder
def fixtures():
    """
    Load fixtures into database
    """
    docker_compose_run('bin/console doctrine:fixtures:load --no-interaction', no_deps=True)


@task
@with_builder
def fetch():
    """
    Fetch conferences
    """
    docker_compose_run('bin/console starfleet:conferences:fetch', no_deps=True)

@task
@with_builder
def remind():
    """
    Remind ending of CFPs
    """
    docker_compose_run('bin/console starfleet:conferences:remind-cfp-ending-soon', no_deps=True)


@task
@with_builder
def cs_fix(dry_run=False):
    """
    Fix coding standards in code
    """
    if dry_run:
        docker_compose_run('./vendor/bin/php-cs-fixer fix --config=.php_cs --dry-run --diff', no_deps=True)
    else:
        docker_compose_run('./vendor/bin/php-cs-fixer fix --config=.php_cs', no_deps=True)


@task
@with_builder
def tests():
    """
    Launch unit and functional tests
    """
    docker_compose_run('bin/console doctrine:fixtures:load --no-interaction', no_deps=True)
    docker_compose_run('SYMFONY_DEPRECATIONS_HELPER=disabled ./vendor/bin/simple-phpunit', no_deps=True)


@task
@with_builder
def builder():
    """
    Bash into a builder container
    """
    docker_compose_run('bash')


@task
@with_builder
def webpack():
    """
    Bash into a builder container
    """
    docker_compose_run('yarn dev')


@task
@with_builder
def webpack_watch():
    """
    Bash into a builder container
    """
    docker_compose_run('yarn watch')


@task
def logs():
    """
    Show logs of infrastructure
    """
    docker_compose('logs -f --tail=150')


@task
def stop():
    """
    Stop the infrastructure
    """
    docker_compose('stop')


@task
@with_builder
def down():
    """
    Clean the infrastructure (remove container, volume, networks)
    """
    docker_compose('down --volumes --rmi=local')


def docker_compose(command_name):
    localEnv = {
        'PROJECT_NAME': env.project_name,
        'PROJECT_DIRECTORY': env.project_directory,
        'PROJECT_HOSTNAMES': env.project_hostnames,
    }

    with shell_env(**localEnv):
        local('docker-compose -p %s %s %s' % (
            env.project_name,
            ' '.join('-f ' + env.root_dir + '/infrastructure/docker/' + file for file in env.compose_files),
            command_name
        ))


def docker_compose_run(command_name, service="builder", user="app", no_deps=False):
    args = [
        'run '
        '--rm '
        '-u %s ' % _shell_escape(user)
    ]

    if no_deps:
        args.append('--no-deps ')

    docker_compose('%s %s /bin/bash -c "%s"' % (
        ' '.join(args),
        _shell_escape(service),
        _shell_escape(command_name)
    ))


def set_local_configuration():
    env.compose_files = ['docker-compose.yml']
    env.user_id = int(local('id -u', capture=True))
    env.root_dir = os.path.dirname(os.path.abspath(__file__))

    if env.user_id > 256000:
        env.user_id = 1000

    with quiet():
        try:
            docker_kernel = "%s" % local('docker version --format "{{.Server.KernelVersion}}"', capture=True)
        except:
            docker_kernel = ''

    if platform == "linux" or platform == "linux2" or docker_kernel.endswith('linuxkit-aufs'):
        env.dinghy = False
    elif platform == "darwin":
        env.dinghy = True


set_local_configuration()
