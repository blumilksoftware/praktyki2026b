<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Rules\NipRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class NipRuleTest extends TestCase
{
    public function testValidNipPasses(): void
    {
        $validator = Validator::make(
            ["nip" => "1234563218"],
            ["nip" => [new NipRule()]],
        );

        $this->assertFalse($validator->fails());
    }

    public function testInvalidChecksumFails(): void
    {
        $validator = Validator::make(
            ["nip" => "1234563219"],
            ["nip" => [new NipRule()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function testNipWithNineDigitsFails(): void
    {
        $validator = Validator::make(
            ["nip" => "123456321"],
            ["nip" => [new NipRule()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function testNipWithElevenDigitsFails(): void
    {
        $validator = Validator::make(
            ["nip" => "12345632181"],
            ["nip" => [new NipRule()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function testNipWithDashesPasses(): void
    {
        $validator = Validator::make(
            ["nip" => "123-456-32-18"],
            ["nip" => [new NipRule()]],
        );

        $this->assertFalse($validator->fails());
    }

    public function testNipWithLeadingZerosPasses(): void
    {
        $validator = Validator::make(
            ["nip" => "0123456789"],
            ["nip" => [new NipRule()]],
        );

        $this->assertFalse($validator->fails());
    }
}
