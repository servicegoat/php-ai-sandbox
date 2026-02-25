<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

if ($argc < 3) {
    echo "Usage: php add-user.php <email> <password>\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email address.\n";
    exit(1);
}

if (strlen($password) > 255) {
    echo "Error: Password cannot be longer than 255 characters.\n";
    exit(1);
}

try {
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Error: User with email $email already exists.\n";
        exit(1);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $uuid = Database::generateUuid();
    $stmt = $pdo->prepare("INSERT INTO users (uuid, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$uuid, $email, $hashedPassword]);

    echo "User $email added successfully with UUID: $uuid\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
