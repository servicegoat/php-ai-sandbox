<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthService;
use App\Database;
use App\LoggerService;

AuthService::requireAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword)) {
        $error = 'Current password is required.';
    } elseif (empty($newPassword)) {
        $error = 'New password is required.';
    } elseif (strlen($newPassword) > 255) {
        $error = 'Password must be no longer than 255 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = Database::getConnection();
            $id = AuthService::getCurrentUserUuid();

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
                LoggerService::getLogger()->warning("Failed password change attempt (incorrect current password) for user: $id");
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $id]);
                
                $success = 'Password updated successfully.';
                LoggerService::getLogger()->info("User changed their password: $id");
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
            LoggerService::getLogger()->error("Error changing password for user " . AuthService::getCurrentUserUuid() . ": " . $e->getMessage());
        }
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .container { max-width: 400px; margin: auto; border: 1px solid #e5e7eb; padding: 24px; border-radius: 8px; background: #f6f8fa; }
        h1 { margin-top: 0; font-size: 24px; }
        label { display: block; margin-bottom: 8px; margin-top: 16px; }
        input[type="password"] { padding: 8px; width: 100%; box-sizing: border-box; }
        button { padding: 8px 12px; margin-top: 20px; width: 100%; cursor: pointer; }
        .error { color: #b00020; margin-top: 12px; font-size: 14px; }
        .success { color: #28a745; margin-top: 12px; font-size: 14px; }
        .nav { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Password</h1>
        <p>User: <?php echo htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8'); ?></p>
        
        <form method="post" action="">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" maxlength="255" required>
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" maxlength="255" required>
            
            <button type="submit">Update Password</button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="nav">
            <a href="/primes.php">Back to Primes</a>
        </div>
    </div>
</body>
</html>
