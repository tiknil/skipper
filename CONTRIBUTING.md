# Contributing to Skipper

This document outlines the development setup and conventions for contributing to Skipper.

## Project Structure

Skipper is a PHP CLI tool built with Symfony components:

- `symfony/console` - Manages CLI commands and interactions
- `symfony/process` - Handles system process execution
- `src/` - Contains the main source code
- `bin/skipper` - Main executable

## Commands

Commands are organized following the Symfony Console component patterns:

- Each command extends `BaseCommand` which provides utility functions
- Commands are registered in `CliApplication` class
- Commands follow a namespace structure (e.g. `proxy:certs`, `proxy:reload`)

Example of a new command:

```php
namespace Tiknil\Skipper\Command;

class ExampleCommand extends BaseCommand
{
    protected static $defaultName = 'example:command';

    protected function configure()
    {
        $this->setDescription('Example command description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Command logic here
        return Command::SUCCESS;
    }
}
```

## Process Execution

System commands are executed using the `ShellCommand` utility class which provides:

- Safe process execution with error handling
- Environment variable management
- Docker compose command wrapping
- Output streaming

Example usage:

```php
use Tiknil\Skipper\Utility\ShellCommand;

ShellCommand::create()->run(['ls', '-ls']);

// Hide command outputs
ShellCommand::create()->showOutput(false)->run(['ls', '-ls']);

// Runs in tty mode
ShellCommand::create()->useTty(true)->run(['ls', '-ls']);

// Use shell integration
ShellCommand::create()->useShellIntegration(true)->run(['cat', 'file.txt', '>', 'other-file.txt']);
```

## Development Setup

1. Clone the repository
2. Install dependencies:

```bash
composer install
```

3. Link for local development:

```json
# Add to your global composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/skipper"
        }
    ]
}
```

```bash
# Install locally
composer global require tiknil/skipper
```
