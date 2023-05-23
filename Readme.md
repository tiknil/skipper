[Skipper](https://github.com/tiknil/skipper) is a tool for managing multiple local Laravel docker-compose deployment.

It is strongly opinionated towards the patterns used by [Tiknil](https://www.tiknil.com) while building a laravel
application.

- [Install & Update](#installation)
- [Usage](#usage)
- [Architecture](#architecture)
- [Mailpit for local emails](#mailpit)
- [Docker compose commands](#docker-compose-commands)
- [Project fields](#project)

### Installation

```
composer global require tiknil/skipper
```

Make sure that the composer binaries directory is in your `$PATH`

You can update Skipper to the latest version by running

```
composer global update tiknil/skipper
```

### Usage

Register the current path as a skipper project:

```bash
# No options, sane defaults will be used and you will be asked to confirm the main fields
skipper init 

# All fields can also be set via command line options
skipper init --host=[host] --name=[name] \
        --compose-file=docker/docker-compose.yml \
        --env-file=docker/.env \
        --http-container=nginx
        --php-container=php-fpm
```

You will be asked to automatically install the default
[laravel docker compose](https://github.com/tiknil/laravel-docker-compose) files.

See [Project](#project) for details about each field.

----
Start the project containers

```bash
skipper sail
```

----

Stops the project containers

```bash
skipper dock
```

----

Install caddy root certificate

```bash
skipper proxy:certs
```

---- 
Once a project is running, skipper provides some useful commands to directly interact with it.

```bash
skipper bash                  # Start a new bash shell inside the PHP container
skipper composer [command...] # Run a composer command (use instead of composer [command]
skipper artisan [command...]  # Run an artisan command (use instead of php artisan [command]
skipper tinker                # Start a new Laravel Tinker shell
skipper backup                # Create a new MySQL backup
skipper restore --file [file] # Restore a MySQL backup
```

Run `skipper` without arguments for a complete list of available commands

### Architecture

Skipper runs a [Caddy](https://caddyserver.com/) container, running as reverse proxy and forwarding
requests to the corresponding project instance. Caddy is also able to generate HTTPS certificates for local domains,
enabling a local environment very similar to a production deployment.

Skipper install its files inside the `~/.skipper` directory.

### Mailpit

Skipper also runs a [Mailpit](https://github.com/axllent/mailpit) container by default. You can see the web
dashboard at [localhost:8025](http://localhost:8025)

Laravel should use the host `host.docker.internal` and port `1025` with driver SMTP:

```
MAIL_MAILER=smtp
MAIL_HOST=host.docker.internal
MAIL_PORT=1025
```

This avoids running a separate mailpit/mailhog instance for each project.

### Docker compose commands

You may need to run custom docker compose command for a project, e.g. `ps` to see running containers.

You can use the `compose` command:

```
skipper compose [command]
```

Basically, replace each `docker-compose [command]` with `skipper compose [command]`.
This is *required* because skipper attaches some options to the docker-compose command that are required, such as the
name or the env file path.

### Project

For the reverse proxy to work, skipper need to know about a project and update the caddy configuration file.
A new project is registered using the `init` command.

> All available projects are registered inside the `~/.skipper/config.yaml` configuration file.

- **name**: The project name is used as the docker compose prefix for each container, volume or network related to the
  project. As a consequence, it must be unique and an update can result in data loss (new docker volumes will be used).
- **host**: The domain to register in the reverse proxy
- **path**: The path to the project root
- **composeFile**: relative path to the docker-compose file.
- **envFile**: relative path to the .env file for the docker-compose file
- **httpContainer**: name of the http container, where caddy should forward the requests
- **phpContainer**: name of the php container, where the utility commands should be run.

