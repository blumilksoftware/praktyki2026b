<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function testHtmlLangMatchesDefaultLocale(): void
    {
        $this->get("/login")
            ->assertOk()
            ->assertSee('lang="' . config("app.locale") . '"', false);
    }

    public function testHtmlLangMatchesSessionLocale(): void
    {
        $this->withSession(["locale" => "pl"])
            ->get("/login")
            ->assertOk()
            ->assertSee('lang="pl"', false);
    }
}
