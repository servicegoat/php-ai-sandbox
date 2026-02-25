<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

if ($argc < 2) {
    echo "Usage: php delete-user.php <email_or_id_or_uuid>\n";
    exit(1);
}

$identifier = $argv[1];

try {
    $pdo = Database::getConnection();
    
    // Find user by email, ID, or UUID
    $stmt = $pdo->prepare("SELECT id, email, uuid FROM users WHERE email = ? OR id = ? OR uuid = ?");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "Error: User not found.\n";
        exit(1);
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);

    echo "User deleted successfully: " . $user['email'] . " (UUID: " . $user['uuid'] . ")\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
