<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="background-color: #f3f4f6; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none;">
<!-- Preheader Text (hidden) -->
<div style="display: none; max-height: 0px; overflow: hidden;">
    Welcome to {{ config('app.name') }} - Complete your account setup to get started...
</div>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 30px 0;">
    <tr>
        <td align="center">
            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 20px;">
                <!-- Header -->
                <tr>
                    <td style="background-color: #151520; padding: 35px 40px; text-align: center;">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo" style="height: 40px; margin-bottom: 20px;">
                        <h1 style="color: #ffffff; font-size: 28px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">
                            Welcome to {{ config('app.name') }}! 🎉
                        </h1>
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding: 40px;">
                        <!-- Welcome Message -->
                        <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 25px;">
                            Hi {{ $name }},
                        </p>
                        <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 25px;">
                            We're thrilled to have you join our team! Your employee account has been successfully created. To get started, please complete your account setup using the button below.
                        </p>

                        <!-- Action Button -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 35px 0;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $setupUrl }}" style="background-color: #151520; border-radius: 8px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: 600; padding: 14px 35px; text-decoration: none; transition: background-color 0.2s;">
                                        Complete Account Setup →
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- Security Notice -->
                        <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; margin: 30px 0; padding: 20px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="24" style="padding-right: 15px;">
                                        <span style="font-size: 24px;">⚠️</span>
                                    </td>
                                    <td>
                                        <p style="color: #991b1b; font-size: 14px; line-height: 1.5; margin: 0;">
                                            <strong>Security Notice:</strong> This activation link expires in 48 hours.
                                            Please complete your setup before the expiration time.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Security Guidelines -->
                        <div style="background-color: #f8fafc; border-radius: 8px; margin: 30px 0; padding: 25px;">
                            <h3 style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0 0 15px;">
                                Security Best Practices
                            </h3>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 12px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="24" style="padding-right: 12px;">
                                                    <span style="font-size: 18px;">🔒</span>
                                                </td>
                                                <td style="color: #475569; font-size: 15px;">
                                                    Never share your activation link or credentials
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="24" style="padding-right: 12px;">
                                                    <span style="font-size: 18px;">📧</span>
                                                </td>
                                                <td style="color: #475569; font-size: 15px;">
                                                    Contact IT Security immediately if you didn't request this
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Support Section -->
                        <div style="border-top: 1px solid #e2e8f0; margin-top: 35px; padding-top: 35px;">
                            <h3 style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0 0 15px;">
                                Need Assistance?
                            </h3>
                            <p style="color: #475569; font-size: 15px; margin: 0 0 15px;">
                                Our support team is available to help you:
                            </p>
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right: 20px;">
                                        <a href="tel:+1234567890" style="color: #151520; display: inline-flex; align-items: center; text-decoration: none;">
                                            <span style="font-size: 18px; margin-right: 8px;">📞</span>
                                            <span style="font-size: 15px;">+1 (234) 567-890</span>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="mailto:support@example.com" style="color: #151520; display: inline-flex; align-items: center; text-decoration: none;">
                                            <span style="font-size: 18px; margin-right: 8px;">✉️</span>
                                            <span style="font-size: 15px;">support@example.com</span>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <table border="0" cellpadding="0" cellspacing="0" width="600">
                <tr>
                    <td style="padding: 0 40px;">
                        <p style="color: #6b7280; font-size: 13px; line-height: 1.5; margin: 0 0 10px; text-align: center;">
                            If you're having trouble with the button above, copy and paste the URL below into your web browser:
                        </p>
                        <p style="color: #151520; font-size: 13px; line-height: 1.5; margin: 0 0 25px; text-align: center; word-break: break-all;">
                            {{ $setupUrl }}
                        </p>
                        <p style="color: #6b7280; font-size: 13px; line-height: 1.5; margin: 0; text-align: center;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
