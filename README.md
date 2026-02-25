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

### Managing Users via CLI

You can manage users in the database using the provided CLI scripts in the `bin/` directory.

#### Adding a User

```bash
php bin/add-user.php <email> <password>
```

#### Editing a User Password

You can update a user's password by providing their email or ID (UUID).

```bash
php bin/edit-user.php <email_or_id> <new_password>
```

#### Deleting a User

You can delete a user by providing their email or ID (UUID).

```bash
php bin/delete-user.php <email_or_id>
```

**Validation Rules:**
- Username must be a valid email address.
- Password must be no longer than 255 characters.

### Logging

To protect user privacy, logs do not contain user emails. Instead, they use the unique ID (UUID) generated for each user.

## Running Tests

To run the unit tests:

```bash
./vendor/bin/phpunit
```
