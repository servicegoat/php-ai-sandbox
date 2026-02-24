<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthService;

AuthService::logout();
header('Location: /login.php');
exit;
