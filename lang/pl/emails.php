<?php

declare(strict_types=1);

return [
    "password_reset" => [
        "oauth_subject" => "Próba resetowania hasła",
        "oauth_title" => "Zaloguj się przez Google",
        "oauth_greeting" => "Cześć :name,",
        "oauth_body" => "Wygląda na to, że próbujesz zresetować hasło, ale Twoje konto jest powiązane z Google. Aby uzyskać dostęp do konta, użyj przycisku Zaloguj się przez Google na stronie logowania.",
        "oauth_cta" => "Przejdź do logowania",
        "oauth_ignore" => "Jeśli nie wysyłałeś tej prośby, możesz zignorować tę wiadomość.",
    ],

    "verification" => [
        "subject" => "Potwierdź swój adres e-mail",
        "title" => "Potwierdź adres e-mail",
        "status_message" => "Adres :email został pomyślnie zarejestrowany.",
        "expiration_message" => "Link weryfikacyjny jest aktywny przez :count godz.",
        "action_text" => "Kliknij poniższy przycisk, aby zweryfikować swój adres e-mail:",
        "button" => "Zweryfikuj adres e-mail",
        "all_rights_reserved" => "Wszelkie prawa zastrzeżone.",
        "accept_mail_subject" => "Weryfikacja Twojej organizacji została zatwierdzona",
        "accept_mail_title" => "Weryfikacja potwierdzona",
        "accept_mail_greeting" => "Cześć :name,",
        "accept_mail_body" => "Świetne wiadomości! Twoja organizacja została pomyślnie zweryfikowana przez nasz zespół administracyjny. Twoje konto jest teraz w pełni aktywne i możesz korzystać ze wszystkich funkcji platformy Applikuj.",
        "accept_mail_cta_text" => "Przejdź do panelu",
        "reject_mail_subject" => "Status Twojej prośby o weryfikację",
        "reject_mail_title" => "Aktualizacja statusu weryfikacji",
        "reject_mail_greeting" => "Cześć :name,",
        "reject_mail_body" => "Dziękujemy za przesłanie prośby o weryfikację organizacji. Niestety, Twoja prośba została przeanalizowana i nie została zatwierdzona w tym momencie.",
        "reject_mail_reason_heading" => "Powód odrzucenia:",
        "reject_mail_next_steps" => "Możesz ponownie złożyć prośbę po usunięciu wskazanych zastrzeżeń. Jeśli masz pytania dotyczące tej decyzji, skontaktuj się z naszym zespołem wsparcia.",
        "reject_mail_support_text" => "Kontakt z wsparciem",
        "already_verified_company" => "Firma została już zweryfikowana.",
        "already_rejected_company" => "Firma została już odrzucona.",
        "already_verified_university" => "Uczelnia została już zweryfikowana.",
        "already_rejected_university" => "Uczelnia została już odrzucona.",
    ],
    "registration" => [
        "subject" => "Witaj w Applikuj! Potwierdź rejestrację",
        "greeting" => "Witaj :name,",
        "body" => "Dziękujemy za założenie konta studenta w Applikuj. Twoje konto zostało pomyślnie utworzone.",
        "details_heading" => "Dane konta:",
        "field_name" => "Imię i nazwisko",
        "field_email" => "Adres e-mail",
        "field_university" => "Uczelnia",
        "ignore_notice" => "Jeśli nie zakładałeś konta, możesz zignorować tę wiadomość.",
        "all_rights_reserved" => "Wszelkie prawa zastrzeżone.",
    ],
];
