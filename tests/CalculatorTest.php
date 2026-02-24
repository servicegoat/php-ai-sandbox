<?php

namespace Tests;

use App\Calculator;
use PHPUnit\Framework\TestCase;
use Exception;

class CalculatorTest extends TestCase
{
    public function testDivide()
    {
        $calculator = new Calculator();
        $this->assertEquals(5, $calculator->divide(10, 2));
    }

    public function testDivideByZero()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot divide by zero");

        $calculator = new Calculator();
        $calculator->divide(5, 0);
    }
}
