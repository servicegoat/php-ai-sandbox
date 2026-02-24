# PHP AI Sandbox

A simple PHP project demonstrating logging, unit testing, and authentication.

## Prerequisites

- PHP 8.0 or higher
- Composer

## Installation

1. Clone the repository.
2. Install dependencies:
   ```bash
   composer install
   ```

## Running the Project

You can run the project using the built-in PHP development server:

```bash
php -S localhost:8000 -t public
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

## Authentication

The project uses a simple SQLite database for authentication.

### Default User

A default user is created automatically:
- **Email:** `brian@olsfamily.com`
- **Password:** `tacos123`

### Adding a Test User

You can add new users to the database using the provided CLI script:

```bash
php bin/add-user.php <email> <password>
```

**Example:**
```bash
php bin/add-user.php john@example.com mysecurepassword
```

**Validation Rules:**
- Username must be a valid email address.
- Password must be no longer than 255 characters.

## Running Tests

To run the unit tests:

```bash
./vendor/bin/phpunit
```
