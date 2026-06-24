<?php

declare(strict_types=1);

return [
    "password_reset" => [
        "oauth_subject" => "Password reset attempt",
        "oauth_title" => "Sign in with Google instead",
        "oauth_greeting" => "Hello :name,",
        "oauth_body" => "It looks like you're trying to reset your password, but your account is linked to Google. To access your account, please use the Sign in with Google button on the login page.",
        "oauth_cta" => "Go to login",
        "oauth_ignore" => "If you didn't request this, you can safely ignore this email.",
    ],

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
];
