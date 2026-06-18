<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Rules\PhoneRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneRuleTest extends TestCase
{
    public static function phoneProvider(): array
    {
        return [
            "valid number 9 digits" => ["123456789", true],
            "valid number with +48 and spaces" => ["+48 123 456 789", true],
            "valid number with 0048 and spaces" => ["0048 123 456 789", true],
            "valid number with 48 and spaces" => ["48 123 456 789", true],
            "valid number with +48, dashes and spaces" => ["+48 123-456-789", true],
            "valid number with 0048, no spaces" => ["0048123456789", true],
            "valid number with +48, no spaces" => ["+48123456789", true],
            "invalid starts with zero prefix" => ["0048023456789", false],
            "invalid starts with zero local" => ["023456789", false],
            "invalid letters" => ["aaaaaa", false],
            "invalid too short" => ["12345", false],
            "invalid too long" => ["12345678901234", false],
        ];
    }

    #[DataProvider("phoneProvider")]
    public function testPhoneValidation(string $phone, bool $expectedPasses): void
    {
        $validator = Validator::make(
            ["phone" => $phone],
            ["phone" => [new PhoneRule()]],
        );

        $this->assertEquals($expectedPasses, !$validator->fails());
    }
}
