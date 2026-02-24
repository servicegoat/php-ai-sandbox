<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthService;

if (AuthService::isAuthenticated()) {
    header('Location: /primes.php');
} else {
    header('Location: /login.php');
}
exit;
