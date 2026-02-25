<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

if ($argc < 3) {
    echo "Usage: php edit-user.php <email_or_id_or_uuid> <new_password>\n";
    exit(1);
}

$identifier = $argv[1];
$newPassword = $argv[2];

if (strlen($newPassword) > 255) {
    echo "Error: Password cannot be longer than 255 characters.\n";
    exit(1);
}

try {
    $pdo = Database::getConnection();
    
    // Find user by email or ID (UUID)
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ? OR id = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "Error: User not found.\n";
        exit(1);
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    echo "Password updated successfully for user: " . $user['email'] . " (ID: " . $user['id'] . ")\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
