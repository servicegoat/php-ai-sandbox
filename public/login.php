<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthService;
use App\LoggerService;

AuthService::startSession();

if (AuthService::isAuthenticated()) {
    header('Location: /primes.php');
    exit;
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) > 255) {
        $error = 'Password must be no longer than 255 characters.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } else {
        if (AuthService::login($email, $password)) {
            $id = AuthService::getCurrentUserUuid();
            LoggerService::getLogger()->info("User logged in: $id");
            header('Location: /primes.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
            LoggerService::getLogger()->warning("Failed login attempt for email: [REDACTED]");
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Login</h1>
        <form method="post" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" maxlength="255" required>
            
            <button type="submit">Login</button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
