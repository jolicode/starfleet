<p align="center">
    <img src="https://starfleet.jolicode.com/build/images/logo.svg" width=100 height=100 alt="Starfleet logo" />
</p>
<h1 align="center">Starfleet</h1>

> Share your conferences activity to your buddies

### Requirements

A Docker environment is provided and requires you to have these tools available:

* Docker
* Bash
* PHP >= 8.1
* [Castor](https://github.com/jolicode/castor#installation)

#### Castor

Once castor is installed, in order to improve your usage of castor scripts, you
can install console autocompletion script.

If you are using bash:

```bash
castor completion | sudo tee /etc/bash_completion.d/castor
```

If you are using something else, please refer to your shell documentation. You
may need to use `castor completion > /to/somewhere`.

Castor supports completion for `bash`, `zsh` & `fish` shells.

### Docker environment

The Docker infrastructure provides a web stack with:
- NGINX
- PostgreSQL
- PHP
- Traefik
- A container with some tooling:
    - Composer
    - Node
    - Yarn / NPM

### Domain configuration (first time only)

Before running the application for the first time, ensure your domain names
point the IP of your Docker daemon by editing your `/etc/hosts` file.

This IP is probably `127.0.0.1` unless you run Docker in a special VM (like docker-machine for example).

Note: The router binds port 80 and 443, that's why it will work with `127.0.0.1`

```
echo '127.0.0.1 local.starfleet.app' | sudo tee -a /etc/hosts
```

### Starting the stack

Launch the stack by running this command:

```bash
castor start
```

> Note: the first start of the stack should take a few minutes.

The site is now accessible at the hostnames your have configured over HTTPS
(you may need to accept self-signed SSL certificate if you do not have mkcert
installed on your computer - see below).

### SSL certificates

HTTPS is supported out of the box. SSL certificates are not versioned and will
be generated the first time you start the infrastructure (`castor start`) or if
you run `castor infra:generate-certificates`.

If you have `mkcert` installed on your computer, it will be used to generate
locally trusted certificates. See [`mkcert` documentation](https://github.com/FiloSottile/mkcert#installation)
to understand how to install it. Do not forget to install CA root from mkcert
by running `mkcert -install`.

If you don't have `mkcert`, then self-signed certificates will instead be
generated with openssl. You can configure [infrastructure/docker/services/router/openssl.cnf](infrastructure/docker/services/router/openssl.cnf)
to tweak certificates.

You can run `castor infra:generate-certificates --force` to recreate new certificates
if some were already generated. Remember to restart the infrastructure to make
use of the new certificates with `castor up` or `castor start`.

### Builder

Having some composer, yarn or other modifications to make on the project?
Start the builder which will give you access to a container with all these
tools available:

```bash
castor builder
```

### Other tasks

Checkout `castor` to have the list of available tasks.

## Usage

By default, the fetchers are not configured and won't fetch anything. You first need to head to the admin and configure them in the `fetcher` menu, then you can run `inv fetch-conferences`.

If you want to add a source, you only have to implement the `FetcherInterface`.

Some fetchers will use tags to fetch their data, and some of these tags may be missing. If this is the case, you should find the fetcher in `src\Fetcher` and add the missing tag to its tags list.

## Slack

Starfleet uses strong integration with Slack as it sends daily Slack notifications. You must [create a Slack application](https://api.slack.com/apps) if you don't have any.
You need to configure a webhook at `https://[your-slack-organization].slack.com/apps/A0F7XDUAZ-incoming-webhooks` and add it to the `SLACK_WEB_HOOK_URL` env variable.
Since Starfleet uses user interaction, you must configure your application at to allow user interaction. You may see how to do it at `https://api.slack.com/interactivity/handling#setup`. Your Slack Signing Secret should be stored in the `SLACK_SIGNING_SECRET` env variable.
During dev, you may use [Ngrok](https://ngrok.com/) to let Slack reach your application. We provide a command to start Ngrok : `inv ngrok`. Paste the resulting address in your Slack app `Request URL` field.

## Map

We provide you with the possibility of adding a map with a marker on every location you and your team attended a conference. If you want to enable this feature, you need to create an account at [Mapbox](https://www.mapbox.com/). You then need to retrieve your API Token and add it to your `.env` file, by replacing the default value of the `MAPBOX_TOKEN` variable.

## Translations

To add easily add new translations, please install and use [i18n-ally](https://plugins.jetbrains.com/plugin/17212-i18n-ally).

## Changes

View the [CHANGELOG](CHANGELOG.md) file attached to this project.

## Sponsor

[![JoliCode](https://jolicode.com/images/logo.svg)](https://jolicode.com)

Open Source time sponsored by JoliCode

## Credits

* [All contributors](https://github.com/jolicode/starfleet/graphs/contributors)

## License

View the [LICENSE](LICENSE) file attached to this project.
