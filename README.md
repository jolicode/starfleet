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

You'll need to configure a Slack web hook url, go to `https://[your-slack-organization].slack.com/apps/A0F7XDUAZ-incoming-webhooks`.

## Usage

By default, the fetchers are not configured and won't fetch anything. You first need to head to the admin and configure them in the `fetcher` menu, then you can run `inv fetch-conferences`.

If you want to add a source, you only have to implement the `FetcherInterface`.

Some fetchers will use tags to fetch their data, and some of these tags may be missing. If this is the case, you should find the fetcher in `src\Fetcher` and add the missing tag to its tags list.

## Changes

View the [CHANGELOG](CHANGELOG.md) file attached to this project.

## Sponsor

[![JoliCode](https://jolicode.com/images/logo.svg)](https://jolicode.com)

Open Source time sponsored by JoliCode

## Credits

* [All contributors](https://github.com/jolicode/starfleet/graphs/contributors)

## License

View the [LICENSE](LICENSE) file attached to this project.
