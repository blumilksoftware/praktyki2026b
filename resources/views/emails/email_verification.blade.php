<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.verification.title') }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 40px 0;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            border: 1px solid #f3f4f6;
        }
        .header-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #9ca3af;
            padding: 30px 40px 0 40px;
            font-weight: 600;
        }
        .content {
            padding: 30px 40px 40px 40px;
        }
        .title {
            font-family: Georgia, Garamond, serif;
            font-size: 28px;
            font-weight: 500;
            color: #111827;
            margin-top: 0;
            margin-bottom: 24px;
            line-height: 1.3;
        }
        .status-message {
            color: #16a34a;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .expiration-message {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }
        .action-text {
            color: #374151;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2d2d2d;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-label">
            Applikuj
        </div>
        <div class="content">
            <h1 class="title">{{ __('emails.verification.title') }}</h1>
            
            <p class="status-message">{{ __('emails.verification.status_message', ['email' => $user->email]) }}</p>
            <p class="expiration-message">{{ __('emails.verification.expiration_message', ['count' => config('auth.verification.expire', 1440) / 60]) }}</p>
            
            <hr class="divider">
            
            <p class="action-text">{{ __('emails.verification.action_text') }}</p>
            
            <a href="{{ url('/email/verify/' . $user->id . '/' . $token) }}" class="button">{{ __('emails.verification.button') }}</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Applikuj. {{ __('emails.verification.all_rights_reserved') }}
        </div>
    </div>
</body>
</html>
