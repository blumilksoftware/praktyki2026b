<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\JobApplication\JobApplicationStatusChangedMail;
use App\Mail\JobApplication\NewJobApplicationMail;
use Tests\TestCase;

class JobApplicationMailTest extends TestCase
{
    public function testNewJobApplicationMailRendersCorrectly(): void
    {
        $mail = new NewJobApplicationMail(
            studentName: "John Doe",
            jobTitle: "Junior PHP Developer",
            applicationUrl: "http://localhost/company/applications/123",
        );

        $mail->assertSeeInHtml("John Doe");
        $mail->assertSeeInHtml("Junior PHP Developer");
        $mail->assertSeeInHtml("http://localhost/company/applications/123");
    }

    public function testJobApplicationStatusChangedMailRendersCorrectly(): void
    {
        $mail = new JobApplicationStatusChangedMail(
            jobTitle: "Junior PHP Developer",
            companyName: "Acme Corp",
            status: "Accepted",
            dashboardUrl: "http://localhost/student/dashboard",
        );

        $mail->assertSeeInHtml("Junior PHP Developer");
        $mail->assertSeeInHtml("Acme Corp");
        $mail->assertSeeInHtml("Accepted");
        $mail->assertSeeInHtml("http://localhost/student/dashboard");
    }
}
