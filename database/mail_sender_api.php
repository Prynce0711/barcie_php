<?php
/**
 * Universal Email Sender - Supports both API and SMTP
 * Automatically chooses the best available method
 */

// Function to send email via SendGrid API
function send_via_sendgrid($to, $subject, $html_body, $config) {
    $api_key = $config['sendgrid_api_key'];
    $from_email = $config['from_email'];
    $from_name = $config['from_name'];
    
    $data = [
        'personalizations' => [[
            'to' => [['email' => $to]]
        ]],
        'from' => [
            'email' => $from_email,
            'name' => $from_name
        ],
        'subject' => $subject,
        'content' => [[
            'type' => 'text/html',
            'value' => $html_body
        ]]
    ];
    
    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        error_log("SendGrid: Email sent successfully to $to");
        return true;
    } else {
        error_log("SendGrid Error: HTTP $http_code - $response");
        return false;
    }
}

// Function to send email via Mailgun API
function send_via_mailgun($to, $subject, $html_body, $config) {
    $api_key = $config['mailgun_api_key'];
    $domain = $config['mailgun_domain'];
    $from_email = $config['from_email'];
    $from_name = $config['from_name'];
    
    $url = "https://api.mailgun.net/v3/$domain/messages";
    
    $data = [
        'from' => "$from_name <$from_email>",
        'to' => $to,
        'subject' => $subject,
        'html' => $html_body
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:$api_key");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        error_log("Mailgun: Email sent successfully to $to");
        return true;
    } else {
        error_log("Mailgun Error: HTTP $http_code - $response");
        return false;
    }
}

// Universal send function that auto-selects best method
function send_email_universal($to, $subject, $body, $altBody = '') {
    global $vendor_available;
    
    // Load config
    $config_path = __DIR__ . '/mail_config_api.php';
    if (!file_exists($config_path)) {
        error_log("Mail config not found");
        return false;
    }
    
    $config = require $config_path;
    $method = $config['method'];
    
    error_log("=== EMAIL SEND ATTEMPT ===");
    error_log("Method: $method");
    error_log("To: $to");
    error_log("Subject: $subject");
    
    try {
        // Try API methods first (more reliable, no SMTP blocks)
        if ($method === 'sendgrid' && !empty($config['sendgrid_api_key'])) {
            return send_via_sendgrid($to, $subject, $body, $config);
        }
        
        if ($method === 'mailgun' && !empty($config['mailgun_api_key']) && !empty($config['mailgun_domain'])) {
            return send_via_mailgun($to, $subject, $body, $config);
        }
        
        // Fallback to SMTP (original method)
        if ($vendor_available && !empty($config['smtp_username']) && !empty($config['smtp_password'])) {
            error_log("Falling back to SMTP");
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Port = $config['smtp_port'];
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);
            $mail->isHTML(true);
            
            $result = $mail->send();
            error_log("SMTP: " . ($result ? "SUCCESS" : "FAILED"));
            return $result;
        }
        
        error_log("ERROR: No email method configured");
        return false;
        
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}
