<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Rules\DomainRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DomainRuleTest extends TestCase
{
    public static function domainProvider(): array
    {
        return [
            "valid standard domain" => ["example.com", true],
            "valid multi level domain" => ["university.edu.pl", true],
            "valid domain with subdomains" => ["sub.domain.co.uk", true],
            "valid short domain" => ["a.pl", true],
            "valid numeric domain" => ["123.456.pl", true],
            "invalid no dot" => ["invalid_domain", false],
            "invalid trailing dot" => ["invalid.", false],
            "invalid leading dot" => [".invalid", false],
            "invalid double dot" => ["invalid..com", false],
            "invalid url with protocol" => ["http://example.com", false],
            "invalid url with path" => ["example.com/path", false],
            "invalid email representation" => ["@example.com", false],
        ];
    }

    #[DataProvider("domainProvider")]
    public function testDomainValidation(string $domain, bool $expectedPasses): void
    {
        $validator = Validator::make(
            ["domain" => $domain],
            ["domain" => [new DomainRule()]],
        );

        $this->assertEquals($expectedPasses, !$validator->fails());
    }
}
