<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Rules\NipRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NipRuleTest extends TestCase
{
    public static function nipProvider(): array
    {
        return [
            "valid NIP" => ["1234563218", true],
            "valid NIP with dashes" => ["123-456-32-18", true],
            "valid NIP with leading zeros" => ["0123456789", true],
            "invalid checksum" => ["1234563219", false],
            "too short" => ["123456321", false],
            "too long" => ["12345632181", false],
            "all zeros" => ["0000000000", false],
        ];
    }

    #[DataProvider("nipProvider")]
    public function testNipValidation(string $nip, bool $expectedPasses): void
    {
        $validator = Validator::make(
            ["nip" => $nip],
            ["nip" => [new NipRule()]],
        );

        $this->assertEquals($expectedPasses, !$validator->fails());
    }
}
