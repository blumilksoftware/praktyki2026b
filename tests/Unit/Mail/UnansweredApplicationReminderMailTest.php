<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\JobApplication\UnansweredApplicationReminderMail;
use ReflectionClass;
use Tests\TestCase;

class UnansweredApplicationReminderMailTest extends TestCase
{
    public function testMailableBuildsCorrectly(): void
    {
        $mail = new UnansweredApplicationReminderMail(
            jobTitle: "PHP Developer",
            companyName: "Acme Corp",
            daysPending: 14,
            applicationUrl: "https://example.com/applications",
        );

        $this->assertEquals(
            __("emails.job_application.reminder_subject", [
                "job_title" => "PHP Developer",
                "days" => 14,
            ]),
            $mail->envelope()->subject,
        );

        $content = $mail->content();
        $this->assertEquals("emails.job_application.unanswered_application_reminder", $content->markdown);

        $this->assertEquals([
            "jobTitle" => "PHP Developer",
            "companyName" => "Acme Corp",
            "daysPending" => 14,
            "applicationUrl" => "https://example.com/applications",
        ], $content->with);
    }

    public function testItHasCorrectLogProperties(): void
    {
        $mail = new UnansweredApplicationReminderMail(
            jobTitle: "PHP Developer",
            companyName: "Acme Corp",
            daysPending: 14,
            applicationUrl: "https://example.com/applications",
        );

        $reflection = new ReflectionClass($mail);

        $methodAction = $reflection->getMethod("getLogAction");
        $methodAction->setAccessible(true);
        $this->assertEquals("send_job_application_reminder_mail", $methodAction->invoke($mail));

        $methodProperties = $reflection->getMethod("getLogProperties");
        $methodProperties->setAccessible(true);

        $this->assertEquals([
            "job_title" => "PHP Developer",
            "company_name" => "Acme Corp",
            "days_pending" => 14,
            "application_url" => "https://example.com/applications",
        ], $methodProperties->invoke($mail));
    }
}
