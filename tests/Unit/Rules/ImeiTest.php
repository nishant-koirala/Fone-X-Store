<?php

namespace Tests\Unit\Rules;

use App\Rules\Imei;
use PHPUnit\Framework\TestCase;

class ImeiTest extends TestCase
{
    public function test_valid_imei_passes(): void
    {
        // 35693803123456 + check digit 1 = 356938031234561
        $this->assertTrue(Imei::isValid('356938031234561'));
        $this->assertSame(1, Imei::computeCheckDigit('35693803123456'));

        // Generated fakes must always be valid
        for ($i = 0; $i < 5; $i++) {
            $fake = Imei::fake();
            $this->assertSame(15, strlen($fake));
            $this->assertTrue(Imei::isValid($fake));
        }
    }

    public function test_invalid_imei_fails(): void
    {
        // Wrong check digit
        $this->assertFalse(Imei::isValid('356938031234562'));

        // 14 digits
        $this->assertFalse(Imei::isValid('35693803123456'));

        // 16 digits
        $this->assertFalse(Imei::isValid('3569380312345610'));

        // Non-numeric
        $this->assertFalse(Imei::isValid('35693803123456A'));
        $this->assertFalse(Imei::isValid(''));
    }

    public function test_rule_validation_callback(): void
    {
        $rule = new Imei;
        $failed = false;

        $rule->validate('imei', '356938031234561', function () use (&$failed) {
            $failed = true;
        });
        $this->assertFalse($failed);

        $rule->validate('imei', '123456789012345', function () use (&$failed) {
            $failed = true;
        });
        $this->assertTrue($failed);
    }
}
