<p align="center">
    <img src="https://starfleet.jolicode.com/build/images/logo.svg" width=100 height=100 alt="Starfleet logo" />
</p>
<h1 align="center">Starfleet</h1>

[![CircleCI](https://circleci.com/gh/jolicode/starfleet.svg?style=svg)](https://circleci.com/gh/jolicode/starfleet)

> Share your conferences activity to your buddies

## Requirements

This project requires:

- Docker 
- [mkcert](https://github.com/FiloSottile/mkcert)
- [pipenv](https://github.com/pypa/pipenv)

## Installation

### Local

First, you will need to add `local.starfleet.app` to your hosts file:
```
127.0.0.1 local.starfleet.app
```

Install pipenv dependencies:
```bash
pipenv install
```

Enter in pipenv shell to get access to `inv` command:
```bash
pipenv shell
```

Generate your SSL certificates with:
```bash
inv generate_certificates
```

If it's your first install of Starfleet, run:
```bash
inv start
```

Open [https://local.starfleet.app](https://local.starfleet.app) in your browser 🚀

If you need to enter a shell to run specific command, run the following command:
```bash
inv builder
```

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

## Changes

View the [CHANGELOG](CHANGELOG.md) file attached to this project.

## Sponsor

[![JoliCode](https://jolicode.com/images/logo.svg)](https://jolicode.com)

Open Source time sponsored by JoliCode

## Credits

* [All contributors](https://github.com/jolicode/starfleet/graphs/contributors)

## License

View the [LICENSE](LICENSE) file attached to this project.
