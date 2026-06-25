<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\Verification\CompanyVerificationAcceptMail;
use App\Mail\Verification\CompanyVerificationRejectMail;
use App\Mail\Verification\UniversityVerificationAcceptMail;
use App\Mail\Verification\UniversityVerificationRejectMail;
use App\Models\Company;
use App\Models\University;
use Tests\TestCase;

class VerificationMailTest extends TestCase
{
    public function testCompanyVerificationAcceptMailRendersCorrectly(): void
    {
        $company = Company::factory()->make(["name" => "Acme Corporation"]);
        $mail = new CompanyVerificationAcceptMail($company);

        $mail->assertSeeInHtml("Acme Corporation");
    }

    public function testCompanyVerificationRejectMailRendersCorrectly(): void
    {
        $company = Company::factory()->make(["name" => "Acme Corporation"]);
        $rejectionReason = "Incomplete documentation provided.";
        $mail = new CompanyVerificationRejectMail($company, $rejectionReason);

        $mail->assertSeeInHtml("Acme Corporation");
        $mail->assertSeeInHtml($rejectionReason);
    }

    public function testUniversityVerificationAcceptMailRendersCorrectly(): void
    {
        $university = University::factory()->make(["name" => "Oxford University"]);
        $mail = new UniversityVerificationAcceptMail($university);

        $mail->assertSeeInHtml("Oxford University");
    }

    public function testUniversityVerificationRejectMailRendersCorrectly(): void
    {
        $university = University::factory()->make(["name" => "Oxford University"]);
        $rejectionReason = "Invalid domain name.";
        $mail = new UniversityVerificationRejectMail($university, $rejectionReason);

        $mail->assertSeeInHtml("Oxford University");
        $mail->assertSeeInHtml($rejectionReason);
    }
}
