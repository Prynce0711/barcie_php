<?php

declare(strict_types=1);

if (!function_exists('create_email_template')) {
    function create_email_template(string $title, string $content, string $footerText = ''): string
    {
        $currentYear = date('Y');

        return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding: 40px 20px;">
                    <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                                <div style="width: 80px; height: 80px; margin: 0 auto 20px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid rgba(255,255,255,0.3);">
                                    <span style="font-size: 40px; color: #ffffff;">&#127970;</span>
                                </div>
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">BarCIE International Center</h1>
                                <p style="margin: 12px 0 0 0; color: #e3f2fd; font-size: 15px; font-weight: 500;">La Consolacion University Philippines</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 45px 35px;">
                                ' . $content . '
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 35px 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 2px solid #e9ecef;">
                                ' . ($footerText ? '<p style="margin: 0 0 20px 0; color: #6c757d; font-size: 14px; line-height: 1.5;">' . $footerText . '</p>' : '') . '
                                <div style="margin-bottom: 20px;">
                                    <p style="margin: 0 0 8px 0; color: #495057; font-size: 15px; font-weight: 600;">Contact Information</p>
                                    <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;"><strong>BarCIE International Center</strong></p>
                                    <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">La Consolacion University Philippines</p>
                                    <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">Email: <a href="mailto:pc.clemente11@gmail.com" style="color: #2a5298; text-decoration: none;">pc.clemente11@gmail.com</a></p>
                                    <p style="margin: 0; color: #6c757d; font-size: 13px;">Phone: [Contact Number] &bull; Hours: Mon-Fri 8:00 AM - 5:00 PM</p>
                                </div>
                                <p style="margin: 0; color: #adb5bd; font-size: 12px;">&copy; ' . $currentYear . ' BarCIE International Center. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    }
}
