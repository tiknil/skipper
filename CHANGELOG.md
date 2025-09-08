# Changelog

All notable changes to `skipper` will be documented in this file.

The skipper release archive can be found on [the github repo](https://github.com/tiknil/skipper/releases)

## 0.3.1 - 2025-09-00

- Fix: removed timeout for tty and shell commands

## 0.3.0 - 2025-09-02

**Features**

- Feature: `alias` command: Easily add or remove host aliases
- Feature: the `sail` command asks to run `init` when used from an unregistred directory

**Chores**

- Chore: Improved development documentation
- Chore: Copilot instructions setup
- Chore: Process handling refactored using `ShellCommand`

## 0.2.3 - 2024-08-22

**Chores**

- Chore: Removed version from docker-compose proxy file, now deprecated

## 0.2.2 - 2024-03-15

**Features**

- Feature: `mysql` command: Start a MySQL shell
- Feature: `ide-helper` command: Perform default ide-helper command for laravel models

## 0.2.1 - 2023-06-30

**Bugfixes**

- Resolved memory issues using `backup` and `restore` commands on larger datasets

## 0.2.0 - 2023-05-24

**Features**

- Feature: host aliases support. You can now add host aliases to a project using `skipper edit`

## 0.1.1 - 2023-05-23

**Improvements**

- Added emoji to commands output

## 0.1.0 - 2023-05-23

- Initial beta release
