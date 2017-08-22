# Tasks Scheduler console 0.9

## Installation
Add an alias to your profile `alias scheduler="php <path to tasks folder>/scheduler"`

## Usage:
- command [options] [arguments]

## Options:
- -h, --help            Display this help message
- -q, --quiet           Do not output any message
- -V, --version         Display this application version
-- --ansi            Force ANSI output
-- --no-ansi         Disable ANSI output
- -n, --no-interaction  Do not ask any interactive question
- -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

## Available commands:
- `help`                 Displays help for a command
- `isitup`               Checks if url is up.
- `list`                 Lists commands
- `bitcoin`
-- `bitcoin:euro`         Returns the bitcoin price in euro.
- `crawl`
-- `crawl:rss`            Crawl url.
- `svn`
-- `svn:log`              Outputs the svn log.
-- `svn:stats`            Outputs svn stats.
