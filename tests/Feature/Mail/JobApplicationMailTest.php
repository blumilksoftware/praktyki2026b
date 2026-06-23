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

        $html = $mail->render();

        $this->assertStringContainsString("John Doe", $html);
        $this->assertStringContainsString("Junior PHP Developer", $html);
        $this->assertStringContainsString("http://localhost/company/applications/123", $html);
    }

    public function testJobApplicationStatusChangedMailRendersCorrectly(): void
    {
        $mail = new JobApplicationStatusChangedMail(
            jobTitle: "Junior PHP Developer",
            companyName: "Acme Corp",
            status: "Accepted",
            dashboardUrl: "http://localhost/student/dashboard",
        );

        $html = $mail->render();

        $this->assertStringContainsString("Junior PHP Developer", $html);
        $this->assertStringContainsString("Acme Corp", $html);
        $this->assertStringContainsString("Accepted", $html);
        $this->assertStringContainsString("http://localhost/student/dashboard", $html);
    }
}
