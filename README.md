<p align="center">
    <img src="https://starfleet.jolicode.com/build/images/logo.svg" width=100 height=100 alt="Starfleet logo" />
</p>
<h1 align="center">Starfleet</h1>

[![CircleCI](https://circleci.com/gh/jolicode/starfleet.svg?style=svg)](https://circleci.com/gh/jolicode/starfleet)

> Share your conferences activity to your buddies

## Installation

### Local

> Add `local.starfleet.app` to your hosts file

Use [mkcert](https://github.com/FiloSottile/mkcert) to install a CA on your system to be able to generate a valid self-signed certificate for local.starfleet.app

`$ mkcert *.starfleet.app`

> certificate and key must be placed in infrastructure/docker/services/router/etc/ssl/certs

Install Python virtualenv to be able to use fabric commands to control easily the Docker stack

`$ pipenv install`

Enter in pipenv shell to get access to `inv` command

`$ pipenv shell`

If it's your first install of Starfleet, run

`$ inv start`

Open [https://local.starfleet.app](https://local.starfleet.app) in your browser 🚀

You'll need to configure a Slack web hook url, go to `https://[your-slack-organization].slack.com/apps/A0F7XDUAZ-incoming-webhooks`.

## Changes

View the [CHANGELOG](CHANGELOG.md) file attached to this project.

## Sponsor

[![JoliCode](https://jolicode.com/images/logo.svg)](https://jolicode.com)

Open Source time sponsored by JoliCode

## Credits

* [All contributors](https://github.com/jolicode/starfleet/graphs/contributors)

## License

View the [LICENSE](LICENSE) file attached to this project.
