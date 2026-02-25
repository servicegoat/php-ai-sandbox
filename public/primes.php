<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthService;
use App\LoggerService;

AuthService::requireAuth();

// Set up custom error handling to prevent leaking details to UI
set_exception_handler(function ($e) {
    LoggerService::getLogger()->error("Uncaught exception: " . $e->getMessage());
    http_response_code(500);
    echo "An internal error occurred. Please check the logs.";
});

function isStrictPositiveIntegerString($value)
{
    // Only digits, no signs, no spaces, no decimals, no scientific notation
    return is_string($value) && preg_match('/^[1-9]\d*$/', $value);
}

function primesUpTo($n)
{
    $n = (int) $n;
    if ($n < 2) {
        return array();
    }

    // Sieve of Eratosthenes
    $isPrime = array_fill(0, $n + 1, true);
    $isPrime[0] = false;
    $isPrime[1] = false;

    $limit = (int) floor(sqrt($n));
    for ($p = 2; $p <= $limit; $p++) {
        if ($isPrime[$p]) {
            for ($multiple = $p * $p; $multiple <= $n; $multiple += $p) {
                $isPrime[$multiple] = false;
            }
        }
    }

    $primes = array();
    for ($i = 2; $i <= $n; $i++) {
        if ($isPrime[$i]) {
            $primes[] = $i;
        }
    }

    return $primes;
}

$input = '';
$error = '';
$primes = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = isset($_POST['n']) ? (string) $_POST['n'] : '';

    if (!isStrictPositiveIntegerString($input)) {
        $error = 'Please enter a single positive integer (e.g., 1, 2, 10, 250).';
        LoggerService::getLogger()->warning('Invalid prime input received: ' . $input);
    } else {
        // Optional guardrail to avoid huge memory/time usage in the browser request
        // Adjust as appropriate for your environment.
        $n = (int) $input;
        if ($n > 2000000) {
            $error = 'Please enter a number less than or equal to 2000000.';
        } else {
            $primes = primesUpTo($n);
        }
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prime Numbers</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Prime Numbers Up To N</h1>

    <div class="primes-nav">
        Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8'); ?></strong> | 
        <a href="edit-password.php">Change Password</a> |
        <a href="logout.php">Logout</a>
    </div>

    <form method="post" action="">
        <label for="n">Enter a single positive integer:</label>
        <input
            type="text"
            id="n"
            name="n"
            inputmode="numeric"
            autocomplete="off"
            pattern="[0-9]+"
            required
            value="<?php echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); ?>"
        >
        <button type="submit" style="margin-top: 10px;">Show primes</button>
        <div id="clientError" class="error" style="display:none;"></div>
    </form>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === ''): ?>
        <div class="result">
            <strong>Primes up to <?php echo (int) $input; ?>:</strong>
            <div class="primes">
                <?php if (count($primes) === 0): ?>
                    None
                <?php else: ?>
                    <?php echo htmlspecialchars(implode(', ', $primes), ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script>
    (function () {
        var input = document.getElementById('n');
        var form = input.form;
        var clientError = document.getElementById('clientError');

        function showError(msg) {
            clientError.textContent = msg;
            clientError.style.display = 'block';
        }

        function clearError() {
            clientError.textContent = '';
            clientError.style.display = 'none';
        }

        // Prevent non-digits during typing
        input.addEventListener('input', function () {
            var cleaned = input.value.replace(/[^0-9]/g, '');
            if (cleaned !== input.value) {
                input.value = cleaned;
            }
            clearError();
        });

        // Validate on submit: positive integer (>= 1)
        form.addEventListener('submit', function (e) {
            var v = input.value;

            // Strict digits only, no empty, no leading +/-, no decimals
            if (!/^[0-9]+$/.test(v)) {
                e.preventDefault();
                showError('Please enter digits only.');
                return;
            }

            // Must be >= 1
            if (v === '0') {
                e.preventDefault();
                showError('Please enter a positive integer (1 or greater).');
                return;
            }

            clearError();
        });
    })();
    </script>
</body>
</html>
