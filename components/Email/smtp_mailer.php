<?php

declare(strict_types=1);

if (!function_exists('barcie_load_phpmailer')) {
    function barcie_load_phpmailer(): bool
    {
        static $loaded = false;
        if ($loaded) {
            return true;
        }

        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer', false)) {
            $loaded = true;
            return true;
        }

        $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($vendorAutoload)) {
            $autoloadFilesPath = __DIR__ . '/../../vendor/composer/autoload_files.php';
            $autoloadUsable = true;

            if (file_exists($autoloadFilesPath)) {
                $autoloadFiles = @include $autoloadFilesPath;
                if (!is_array($autoloadFiles)) {
                    $autoloadUsable = false;
                } else {
                    foreach ($autoloadFiles as $autoloadFile) {
                        if (!file_exists((string) $autoloadFile)) {
                            $autoloadUsable = false;
                            error_log('Email warning: Composer autoload dependency missing: ' . $autoloadFile . '. Falling back to direct PHPMailer include.');
                            break;
                        }
                    }
                }
            }

            if ($autoloadUsable) {
                try {
                    require_once $vendorAutoload;
                } catch (\Throwable $e) {
                    error_log('Email warning: Composer autoload failed. Falling back to direct PHPMailer include. Error: ' . $e->getMessage());
                }
            }
        }

        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer', false)) {
            $loaded = true;
            return true;
        }

        $phpmailerSrc = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/';
        $requiredFiles = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
        foreach ($requiredFiles as $file) {
            $fullPath = $phpmailerSrc . $file;
            if (!file_exists($fullPath)) {
                error_log('Email warning: Missing PHPMailer source file: ' . $fullPath);
                return false;
            }
            require_once $fullPath;
        }

        $loaded = class_exists('\\PHPMailer\\PHPMailer\\PHPMailer', false);
        if (!$loaded) {
            error_log('Email warning: PHPMailer class is unavailable after fallback load.');
        }
        return $loaded;
    }
}

if (!function_exists('send_smtp_mail')) {
    function send_smtp_mail(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        if (!barcie_load_phpmailer()) {
            error_log('Email skipped: PHPMailer dependencies are unavailable.');
            return true;
        }

        $configPath = __DIR__ . '/../../database/mail_config.php';
        if (!file_exists($configPath)) {
            error_log('ERROR: Mail config file not found at: ' . $configPath);
            return false;
        }

        $config = require $configPath;
        $debugLog = defined('LOG_PATH') && !empty(LOG_PATH) ? LOG_PATH . '/email_debug.log' : '';

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port = (int) $config['port'];
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->isHTML(true);

            if (defined('DEBUG_MODE') && DEBUG_MODE && !empty($debugLog)) {
                if (!is_dir(dirname($debugLog))) {
                    @mkdir(dirname($debugLog), 0777, true);
                }
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function ($str) use ($debugLog): void {
                    error_log('PHPMailer: ' . $str);
                    @file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . 'PHPMailer: ' . $str . PHP_EOL, FILE_APPEND);
                };
            } else {
                $mail->SMTPDebug = 0;
            }

            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody !== '' ? $altBody : strip_tags($body);

            $result = $mail->send();
            error_log('Email sent successfully to: ' . $to);
            return (bool) $result;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
            if (!empty($debugLog)) {
                @file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . 'PHPMailer error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
            return false;
        } catch (\Throwable $e) {
            error_log('Email failed: ' . $e->getMessage());
            return false;
        }
    }
}
