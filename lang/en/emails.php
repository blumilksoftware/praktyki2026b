<?php

declare(strict_types=1);

return [
    "verification" => [
        "subject" => "Confirm your email address",
        "title" => "Confirm your email address",
        "status_message" => "We sent the activation link to: :email",
        "expiration_message" => "Remember that the link expires after :count hours.",
        "action_text" => "To activate your account in Applikuj service, click the button below:",
        "button" => "Confirm email",
        "all_rights_reserved" => "All rights reserved.",
        "accept_mail_subject" => "Your organization verification has been approved",
        "accept_mail_title" => "Verification approved",
        "accept_mail_greeting" => "Hello :name,",
        "accept_mail_body" => "Great news! Your organization has been successfully verified by our admin team. Your account is now fully activated and you can access all features of the Applikuj platform.",
        "accept_mail_cta_text" => "Go to your dashboard",
        "reject_mail_subject" => "Your organization verification request",
        "reject_mail_title" => "Verification status update",
        "reject_mail_greeting" => "Hello :name,",
        "reject_mail_body" => "Thank you for submitting your organization for verification. Unfortunately, your verification request has been reviewed and was not approved at this time.",
        "reject_mail_reason_heading" => "Reason for rejection:",
        "reject_mail_next_steps" => "You can reapply after addressing the concern. If you have any questions about this decision, please contact our support team.",
        "reject_mail_support_text" => "Contact support",
        "already_verified_company" => "Company is already verified.",
        "already_rejected_company" => "Company is already rejected.",
        "already_verified_university" => "University is already verified.",
        "already_rejected_university" => "University is already rejected.",
    ],

    "job_application" => [
        "new_subject" => "New application for :job_title",
        "new_title" => "New Application",
        "new_body" => "Student :student_name has applied for your job offer: :job_title.",
        "new_cta" => "View Application",
        "status_changed_subject" => "Status change for your application: :job_title",
        "status_changed_title" => "Application Status Updated",
        "status_changed_body" => "Company :company_name has changed the status of your application for :job_title to: :status.",
        "status_changed_cta" => "Go to dashboard",
    ],
];
