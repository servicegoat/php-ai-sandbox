# PHP AI Sandbox

A simple PHP project demonstrating logging and unit testing.

## Prerequisites

- PHP >= 8.0
- [Composer](https://getcomposer.org/)

## Installation

1. Clone the repository:
   ```bash
   git clone git@github.com:servicegoat/php-ai-sandbox.git
   cd php-ai-sandbox
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

## Running Locally

You can run the project using the built-in PHP development server:

```bash
php -S localhost:8000 -t public
```

Then, open your browser and navigate to `http://localhost:8000`.

## Running Tests

To run the unit tests, use the following command:

```bash
./vendor/bin/phpunit
```

## Project Structure

- `src/`: Contains the application logic.
- `public/`: Contains the entry point and web assets.
- `tests/`: Contains the unit tests.
- `logs/`: Directory for application logs.
