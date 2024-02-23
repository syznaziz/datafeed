# DataFeed System

The DataFeed system is a PHP command-line tool for parsing XML data and pushing it into a database.

## Features

- Supports importing data from XML (or CSV)
- Configurable database connections for various database types.
- Automatic table creation based on XML structure.
- Error logging to a specified log file.

## Installation

1. Clone the repository.
2. Modify the `config.php` file with your database details.
3. Run `php datafeed.php config.php` to execute the script.

## Configuration

- Edit `config.php` to set database connections and other settings.
- Specify the data source type and file path in the configuration.

## Usage

Run the script from the command line:

```bash
php datafeed.php config.php
