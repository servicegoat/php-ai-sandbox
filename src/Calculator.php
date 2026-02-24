<?php

namespace App;

use Exception;

class Calculator
{
    public function divide(int $a, int $b): float
    {
        if ($b === 0) {
            LoggerService::getLogger()->error("Division by zero attempted with $a / $b");
            throw new Exception("Cannot divide by zero");
        }

        LoggerService::getLogger()->info("Dividing $a by $b");
        return $a / $b;
    }
}
